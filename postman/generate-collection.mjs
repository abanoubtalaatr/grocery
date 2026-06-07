import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { execSync } from 'node:child_process';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const groceryRoot = path.resolve(__dirname, '..');

const routesJson = execSync('/opt/homebrew/opt/php@8.3/bin/php artisan route:list --json', {
  cwd: groceryRoot,
  encoding: 'utf8',
});

const routes = JSON.parse(routesJson).filter((route) => route.uri.startsWith('api'));

const bodyMap = {
  'api/auth/register': {
    username: 'johndoe',
    firstname: 'John',
    lastname: 'Doe',
    email: 'john@example.com',
    phone: '+201234567890',
    password: 'Password123!',
    password_confirmation: 'Password123!',
  },
  'api/auth/login': {
    email: 'john@example.com',
    password: 'Password123!',
  },
  'api/auth/forgot-password': { email: 'john@example.com' },
  'api/auth/verify-otp': { email: 'john@example.com', otp: '123456' },
  'api/auth/reset-password': {
    email: 'john@example.com',
    otp: '123456',
    password: 'NewPassword123!',
    password_confirmation: 'NewPassword123!',
  },
  'api/auth/google': { token: 'google-id-token' },
  'api/auth/change-password': {
    current_password: 'Password123!',
    password: 'NewPassword123!',
    password_confirmation: 'NewPassword123!',
  },
  'api/profile/info': {
    firstname: 'John',
    lastname: 'Doe',
    phone: '+201234567890',
  },
  'api/addresses': {
    label: 'Home',
    full_name: 'John Doe',
    phone: '+201234567890',
    street_address: '123 Main St',
    city: 'Cairo',
    country: 'Egypt',
    is_default: true,
  },
  'api/language': { language: 'en' },
  'api/appearance': { theme: 'dark' },
  'api/notification-preferences': {
    order_updates: true,
    promotion_emails: false,
    nutrition_insights: true,
    price_alerts: true,
  },
  'api/support/report': {
    issue_type: 'Order issue',
    order_number: 'ORD-1001',
    message: 'My order arrived damaged and I need assistance.',
  },
  'api/cart/items': { meal_id: 1, quantity: 2 },
  'api/chatbot': { message: 'What meals do you recommend today?' },
  'api/contact': {
    name: 'John Doe',
    email: 'john@example.com',
    subject: 'General inquiry',
    message: 'I would like to know more about delivery areas.',
  },
  'api/orders': {
    address_id: 1,
    payment_method: 'cash',
    notes: 'Leave at door',
  },
  'api/payments/stripe/checkout-session': { order_id: 1 },
  'api/smart-lists': { name: 'Weekly groceries', description: 'My weekly list' },
  'api/smart-lists/{id}/meals': { meal_id: 1, quantity: 1 },
  'api/notification-settings': {
    email_notifications: true,
    push_notifications: true,
    sms_notifications: false,
  },
  'api/notifications/mark-all-read': {},
  'api/notifications/delete-multiple': { ids: [1, 2, 3] },
};

const descriptionMap = {
  'api/language': 'Get the authenticated user app language preference (en|ar).',
  'api/language|PUT': 'Update app language. Body: { "language": "en" | "ar" }',
  'api/appearance': 'Get theme preference (light|dark).',
  'api/appearance|PUT': 'Update theme. Body: { "theme": "light" | "dark" }',
  'api/notification-preferences':
    'Get notification toggles for settings page (order_updates, promotion_emails, nutrition_insights, price_alerts).',
  'api/notification-preferences|PUT': 'Update notification preference toggles.',
  'api/data-management/download':
    'Download a JSON export of profile, addresses, recent orders, and notification preferences.',
  'api/data-management/delete': 'Permanently delete the authenticated user account and related data.',
  'api/loyalty': 'Get loyalty points, membership tier, benefits, and active coupons.',
  'api/support/report': 'Submit a support/problem report (authenticated).',
  'api/faqs': 'Paginated FAQs list. Query: ?page=1',
};

function folderForUri(uri) {
  const parts = uri.split('/');
  if (parts[0] !== 'api') return 'Other';
  if (parts.length === 1) return 'API Root';

  const segment = parts[1];

  const map = {
    health: 'Health',
    auth: 'Auth',
    profile: 'Profile',
    addresses: 'Addresses',
    language: 'App Settings',
    appearance: 'App Settings',
    'notification-preferences': 'App Settings',
    'data-management': 'App Settings',
    'notification-settings': 'Notification Settings (Legacy)',
    notifications: 'Notifications Inbox',
    'smart-lists': 'Smart Lists',
    cart: 'Cart',
    favorites: 'Favorites',
    chatbot: 'Chatbot',
    cards: 'Stripe & Cards',
    'setup-intent': 'Stripe & Cards',
    'charge-card': 'Stripe & Cards',
    orders: 'Orders',
    payments: 'Payments',
    dashboard: 'Dashboard',
    loyalty: 'Loyalty',
    support: 'Support',
    frequency: 'Meals',
    meals: 'Meals',
    categories: 'Categories',
    subcategories: 'Subcategories',
    offers: 'Offers',
    faqs: 'Support & Content',
    pages: 'Support & Content',
    contact: 'Support & Content',
    settings: 'App Config',
    'special-notes': 'App Config',
    'new-products': 'Meals',
    'best-sells': 'Meals',
    sliders: 'Meals',
    brands: 'Meals',
    'more-to-explore': 'Meals',
    stripe: 'Stripe & Cards',
  };

  return map[segment] ?? 'Other';
}

function isProtected(route) {
  return route.middleware.some((m) => m.includes('Authenticate:sanctum'));
}

function normalizeMethod(method) {
  return method.split('|')[0].toUpperCase();
}

function buildUrl(uri) {
  const clean = uri.replace(/\{([^}]+)\}/g, '{{$1}}');
  return `{{base_url}}/${clean}`;
}

function buildRequest(route) {
  const method = normalizeMethod(route.method);
  const uri = route.uri;
  const key = `${uri}|${method}`;
  const bodyKey = Object.keys(bodyMap).find((pattern) => {
    const regex = new RegExp(`^${pattern.replace(/\{[^}]+\}/g, '[^/]+')}$`);
    return regex.test(uri);
  });

  const request = {
    method,
    header: [{ key: 'Accept', value: 'application/json' }],
    url: buildUrl(uri),
    description: descriptionMap[key] ?? descriptionMap[uri] ?? route.action,
  };

  if (isProtected(route)) {
    request.auth = {
      type: 'bearer',
      bearer: [{ key: 'token', value: '{{token}}', type: 'string' }],
    };
  }

  if (['POST', 'PUT', 'PATCH'].includes(method)) {
    request.header.push({ key: 'Content-Type', value: 'application/json' });
    const raw = JSON.stringify(bodyMap[bodyKey] ?? {}, null, 2);
    request.body = { mode: 'raw', raw };
  }

  if (uri === 'api/profile/image' && method === 'POST') {
    request.body = {
      mode: 'formdata',
      formdata: [{ key: 'image', type: 'file', src: [] }],
    };
    request.header = request.header.filter((h) => h.key !== 'Content-Type');
  }

  if (uri === 'api/data-management/download') {
    request.description =
      descriptionMap[uri] +
      ' Returns application/json file download (not wrapped in success/data envelope).';
  }

  return {
    name: `${method} ${uri}`,
    request,
    event: buildEvents(uri, method),
    response: [],
  };
}

function buildEvents(uri, method) {
  const events = [];

  if (uri === 'api/auth/login' || uri === 'api/auth/register') {
    events.push({
      listen: 'test',
      script: {
        type: 'text/javascript',
        exec: [
          "const json = pm.response.json();",
          "const token = json?.data?.token ?? json?.token;",
          "if (token) {",
          "  pm.collectionVariables.set('token', token);",
          "  pm.environment.set('token', token);",
          "  console.log('Saved bearer token to collection/environment variables.');",
          "}",
          "pm.test('Status is 200 or 201', function () {",
          "  pm.expect(pm.response.code).to.be.oneOf([200, 201]);",
          "});",
        ],
      },
    });
  }

  if (
    ['api/language', 'api/appearance', 'api/notification-preferences'].includes(uri) &&
    method === 'GET'
  ) {
    events.push({
      listen: 'test',
      script: {
        type: 'text/javascript',
        exec: [
          "pm.test('Response has success flag', () => pm.expect(pm.response.json().success).to.eql(true));",
          "pm.test('Response has data object', () => pm.expect(pm.response.json().data).to.be.an('object'));",
        ],
      },
    });
  }

  return events.length ? events : undefined;
}

const folderBuckets = new Map();

for (const route of routes) {
  const folder = folderForUri(route.uri);
  if (!folderBuckets.has(folder)) folderBuckets.set(folder, []);
  folderBuckets.get(folder).push(buildRequest(route));
}

function stripUndefined(obj) {
  return JSON.parse(JSON.stringify(obj));
}

const folderOrder = [
  'Health',
  'Auth',
  'Profile',
  'Addresses',
  'App Settings',
  'Notification Settings (Legacy)',
  'Notifications Inbox',
  'Dashboard',
  'Loyalty',
  'Support',
  'Support & Content',
  'Cart',
  'Favorites',
  'Smart Lists',
  'Orders',
  'Payments',
  'Stripe & Cards',
  'Chatbot',
  'Meals',
  'Categories',
  'Subcategories',
  'Offers',
  'App Config',
  'Other',
];

const items = folderOrder
  .filter((name) => folderBuckets.has(name))
  .map((name) => ({
    name,
    item: folderBuckets.get(name).sort((a, b) => a.name.localeCompare(b.name)).map(stripUndefined),
  }));

for (const [name, folderItems] of folderBuckets.entries()) {
  if (!folderOrder.includes(name)) {
    items.push({ name, item: folderItems.sort((a, b) => a.name.localeCompare(b.name)).map(stripUndefined) });
  }
}

const collection = {
  info: {
    _postman_id: 'grocery-api-collection',
    name: 'Grocery API',
    description:
      'Complete Grocery Laravel API collection. Set `base_url` (e.g. http://localhost:8000) and `token` from login/register response. Protected routes use Bearer auth.',
    schema: 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
  },
  auth: {
    type: 'bearer',
    bearer: [{ key: 'token', value: '{{token}}', type: 'string' }],
  },
  variable: [
    { key: 'base_url', value: 'http://localhost:8000' },
    { key: 'token', value: '' },
  ],
  item: items,
};

const environment = {
  id: 'grocery-api-local',
  name: 'Grocery API - Local',
  values: [
    { key: 'base_url', value: 'http://localhost:8000', type: 'default', enabled: true },
    { key: 'token', value: '', type: 'secret', enabled: true },
  ],
  _postman_variable_scope: 'environment',
  _postman_exported_at: new Date().toISOString(),
  _postman_exported_using: 'generate-collection.mjs',
};

fs.mkdirSync(__dirname, { recursive: true });
fs.writeFileSync(path.join(__dirname, 'Grocery-API.postman_collection.json'), JSON.stringify(collection, null, 2));
fs.writeFileSync(path.join(__dirname, 'Grocery-API.postman_environment.json'), JSON.stringify(environment, null, 2));

console.log(`Generated ${routes.length} API requests in ${items.length} folders.`);
