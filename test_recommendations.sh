#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}=================================${NC}"
echo -e "${BLUE}Testing Meal Recommendations API${NC}"
echo -e "${BLUE}=================================${NC}\n"

# Base URL
BASE_URL="http://127.0.0.1:8001/api"

# Step 1: Check if user exists, if not register
echo -e "${GREEN}Step 1: Creating test user...${NC}"
REGISTER_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/register" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "test@example.com",
    "username": "testuser",
    "password": "Test123456",
    "password_confirmation": "Test123456",
    "agree_terms": true
  }')

# Step 2: Login
echo -e "${GREEN}Step 2: Logging in...${NC}"
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "login": "test@example.com",
    "password": "Test123456"
  }')

# Extract token
TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"token":"[^"]*' | sed 's/"token":"//')

if [ -z "$TOKEN" ]; then
    echo -e "${RED}Failed to get authentication token${NC}"
    echo "Response: $LOGIN_RESPONSE"
    exit 1
fi

echo -e "${GREEN}✓ Successfully logged in${NC}"
echo -e "Token: ${TOKEN:0:20}...\n"

# Step 3: Get Recommendations (limit 5)
echo -e "${GREEN}Step 3: Getting meal recommendations (limit: 5)...${NC}"
RECOMMENDATIONS=$(curl -s -X GET "$BASE_URL/meals/recommendations?limit=5" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")

echo -e "${BLUE}Response:${NC}"
echo "$RECOMMENDATIONS" | python3 -m json.tool

# Step 4: Get Recommendations (limit 10)
echo -e "\n${GREEN}Step 4: Getting more recommendations (limit: 10)...${NC}"
RECOMMENDATIONS_10=$(curl -s -X GET "$BASE_URL/meals/recommendations?limit=10" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")

echo -e "${BLUE}Response:${NC}"
echo "$RECOMMENDATIONS_10" | python3 -m json.tool

echo -e "\n${GREEN}✓ Test completed!${NC}"
