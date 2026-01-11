# 🤖 Chatbot API - Complete!

## ✅ Implementation Summary

I've successfully implemented a chatbot API that uses Google's Gemini AI to answer questions about meals in your grocery app.

---

## 🎯 Features

✅ **AI-Powered Chatbot** - Uses Google Gemini 2.5 Flash Lite  
✅ **Meal Context** - Automatically includes all available meals  
✅ **Smart Responses** - Answers questions about meals, prices, categories, etc.  
✅ **Secure** - API key stored in .env  
✅ **Error Handling** - Comprehensive error handling and logging  

---

## 📊 API Endpoint

### Chat with AI
```
POST /api/chatbot
```

**Authentication:** Required (Bearer Token)

**Headers:**
```
Authorization: Bearer {your_token}
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
    "question": "What meals do you have under $15?"
}
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Chat response generated successfully",
    "data": {
        "question": "What meals do you have under $15?",
        "answer": "Certainly! Here are the meals we have available for under $15:\n\n*   **Fresh Organic Salad Mix** - $10.39 (on sale from $12.99)\n*   **Grilled Vegetable Platter** - $12.99 (on sale from $15.99)\n*   **Tropical Fruit Bowl** - $14.99 (on sale from $18.99)\n*   **Greek Yogurt Parfait** - $6.99 (on sale from $8.99)\n*   **Artisan Bread Selection** - $7.99\n\nLet me know if you'd like to explore any of these further!",
        "meals_count": 9
    }
}
```

---

## 🔧 Configuration

### Environment Variable

Add to your `.env` file:
```env
GEMINI_API_KEY=your_gemini_api_key_here
```

**Note:** The API key has been added to your `.env` file automatically.

### Get Your API Key

1. Go to [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Create a new API key
3. Copy the key
4. Add it to your `.env` file

---

## 🧪 Test Results

### ✅ Test 1: Price Filter Question
**Question:** "What meals do you have under $15?"

**Result:** ✅ Successfully identified 5 meals under $15 with prices and offers

### ✅ Test 2: Category Question
**Question:** "What vegetarian meals do you have?"

**Result:** ✅ Successfully identified vegetarian options from the menu

---

## 📱 Mobile App Integration

### Example Usage
```javascript
// Chat with AI about meals
const askChatbot = async (question) => {
    try {
        const response = await api.post('/chatbot', {
            question: question
        });
        
        if (response.success) {
            displayMessage(response.data.answer);
        } else {
            showError(response.message);
        }
    } catch (error) {
        showError('Failed to get response from chatbot');
    }
};

// Example questions
askChatbot("What meals do you have under $15?");
askChatbot("What vegetarian options are available?");
askChatbot("Show me meals with high ratings");
askChatbot("What's the cheapest meal?");
```

### Chat UI Component
```javascript
<ChatScreen>
    <MessageList>
        {messages.map((msg, index) => (
            <Message 
                key={index}
                type={msg.type} // 'user' or 'bot'
                text={msg.text}
            />
        ))}
    </MessageList>
    
    <Input
        placeholder="Ask about meals..."
        onSubmit={(text) => {
            addUserMessage(text);
            askChatbot(text);
        }}
    />
</ChatScreen>
```

---

## 🎨 What the Chatbot Can Do

The chatbot has access to all meal information including:

- **Meal Details**: Title, description, price, discount price
- **Ratings**: Rating and rating count
- **Categories**: Category and subcategory
- **Stock**: Stock quantity and availability
- **Offers**: Offer titles and discount information
- **Brand & Size**: Brand name and product size

### Example Questions Users Can Ask:

1. **Price Questions:**
   - "What meals do you have under $15?"
   - "What's the cheapest meal?"
   - "Show me meals between $10 and $20"

2. **Category Questions:**
   - "What vegetarian meals do you have?"
   - "Show me fruits"
   - "What's in the bakery category?"

3. **Rating Questions:**
   - "What are your highest rated meals?"
   - "Show me meals with 4.5+ stars"

4. **General Questions:**
   - "What meals are on sale?"
   - "What's featured today?"
   - "What meals are in stock?"

---

## 🔍 How It Works

1. **User sends question** via POST request
2. **System fetches all meals** from database with full details
3. **Meals data formatted** as JSON
4. **Prompt created** with:
   - All meals data
   - User's question
   - Instructions for the AI
5. **Request sent to Gemini API** with the prompt
6. **AI generates response** based on meals data
7. **Response returned** to the user

---

## ⚙️ Technical Details

### Controller
- **Location:** `app/Http/Controllers/Api/ChatbotController.php`
- **Method:** `chat()`

### Gemini API Configuration
- **Model:** `gemini-2.5-flash-lite`
- **Temperature:** 0.7 (balanced creativity)
- **Max Tokens:** 1024
- **Timeout:** 30 seconds

### Data Sent to AI
The chatbot sends:
- All available meals
- Meal details (price, rating, category, etc.)
- User's question
- Context about being a grocery app assistant

---

## 🛡️ Error Handling

### API Key Missing
```json
{
    "success": false,
    "message": "Gemini API key is not configured"
}
```

### API Request Failed
```json
{
    "success": false,
    "message": "Failed to get response from AI",
    "error": "Invalid API key" // or "API request failed"
}
```

### Validation Error
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "question": ["The question field is required."]
    }
}
```

---

## 📝 Request Validation

| Field | Type | Required | Max Length | Description |
|-------|------|----------|------------|-------------|
| `question` | string | Yes | 1000 | User's question |

---

## 🎯 Use Cases

### 1. Product Discovery
Users can ask natural language questions to find meals:
- "What healthy options do you have?"
- "Show me breakfast items"

### 2. Price Comparison
- "What's the cheapest meal?"
- "Show me meals under $20"

### 3. Dietary Preferences
- "What vegetarian meals are available?"
- "Do you have gluten-free options?"

### 4. Recommendations
- "What do you recommend for dinner?"
- "What's popular right now?"

---

## 🚀 Performance

- **Response Time:** ~2-5 seconds (depends on Gemini API)
- **Timeout:** 30 seconds
- **Meals Loaded:** All available meals (filtered by availability)
- **Data Size:** Optimized JSON format

---

## 🔐 Security

✅ **API Key in .env** - Never exposed in code  
✅ **Authentication Required** - Only authenticated users can chat  
✅ **Input Validation** - Question length and format validated  
✅ **Error Logging** - Errors logged without exposing sensitive data  
✅ **Timeout Protection** - Prevents hanging requests  

---

## 📚 Files Created/Modified

### Created
- `app/Http/Controllers/Api/ChatbotController.php` - Chatbot controller

### Modified
- `routes/api.php` - Added chatbot route
- `.env` - Added GEMINI_API_KEY

---

## ✅ What's Working

✅ **Chatbot endpoint** created  
✅ **Gemini API integration** working  
✅ **Meals data** automatically included  
✅ **API key** configured in .env  
✅ **Error handling** comprehensive  
✅ **Validation** working  
✅ **Tested** with multiple questions  
✅ **Response formatting** correct  

---

## 🎉 Summary

Your grocery app now has an **AI-powered chatbot** that can:

✅ Answer questions about meals  
✅ Help users find products by price, category, rating  
✅ Provide recommendations  
✅ Understand natural language queries  
✅ Access real-time meal data  

**Ready for mobile app integration!** 🚀📱

Users can now chat naturally with your app to discover meals, compare prices, and get recommendations!
