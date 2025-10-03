# CoolBeans Library Search - Laravel MCP Server

A comprehensive **Model Context Protocol (MCP) server** built with **Laravel** that provides intelligent search capabilities and content summarization through the CoolBeans Library database. This server enables AI assistants to search through documents, books, lectures, and other content with advanced filtering, smart search modes, and full content access capabilities.

## 🚀 Features

### 📚 **CoolBeans Library Search & Content Access**
- **🧠 Intelligent Search Modes**: Comprehensive, name-only, content-only, exclude-content, and partial-match searches with automatic mode detection
- **📄 Full Content Summaries**: Get complete content of books/documents by name or ID with smart excerpting for large files
- **🔍 Advanced Search Fields**: Search across 15+ database fields including titles, descriptions, content, notes, lecture codes, file labels, and more
- **🎯 Smart Query Understanding**: Handles natural language like "books about prayer", "don't look through content", "file named X"
- **📊 Enhanced Results**: All results include database IDs, descriptions, content length indicators, and actionable tips
- **🔧 Multiple Response Formats**: Summary (with IDs), detailed list, simple list, and full content summary modes
- **⚡ Performance Tracking**: Monitor search performance, response times, and content access patterns
- **📋 Complete Request History**: Full audit trail of searches and content access with exportable results

### 🛠️ **Additional Tools**
- **Calculator Tool**: Basic mathematical operations (add, subtract, multiply, divide)
- **Text Processor Tool**: Text manipulation operations (uppercase, lowercase, reverse, word count, etc.)
- **Weather Tool**: Simulated weather information for any location

### 📊 **Web Interface**
- **Modern UI**: Clean, responsive interface built with Tailwind CSS
- **Real-time Search**: AJAX-powered search without page refreshes
- **Statistics Dashboard**: Overview of files, searches, and system metrics
- **Export Capabilities**: Export search results to CSV format
- **Mobile Friendly**: Fully responsive design for all devices

### 🔐 **Authentication & Security**
- OAuth integration support
- Laravel Sanctum token authentication
- Custom authentication middleware
- Request tracking and monitoring

## 📋 Requirements

- **PHP**: ^8.2
- **Laravel**: ^12.0
- **Laravel MCP**: ^0.2.0
- **MySQL**: 5.7+ or 8.0+
- **Composer**: Latest version

## 🛠️ Installation

### 1. Clone and Setup
```bash
git clone <repository-url>
cd laravel-mcp-test-v1
composer install
cp .env.example .env
php artisan key:generate
```

### 2. Database Configuration
Update your `.env` file to connect to your MySQL database containing the files table:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_coolbeans_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Run Migrations
```bash
php artisan migrate
```

This will create the `mcp_requests` table to track search requests. Your existing `files` table will be used for the library content.

### 4. Configure Environment
Update your `.env` file with appropriate settings:
```env
APP_NAME="CoolBeans Library Search"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
```

## 🚀 Usage

### Web Interface

#### 1. Start the Application
```bash
php artisan serve
```

#### 2. Access the Search Interface
Visit `http://localhost:8000` to access the CoolBeans Library search interface.

#### 3. Perform Searches
- Enter search queries like "books about prayer from coolbeans"
- Use optional filters for source and type
- Choose response format (summary, list, detailed)
- Set result limits (1-50 results)

### MCP Server Integration

#### 1. Start the MCP Server
```bash
php artisan mcp:start test
```

#### 2. Configure Claude Desktop
Add this configuration to your Claude Desktop config file (`~/Library/Application Support/Claude/claude_desktop_config.json`):

```json
{
  "mcpServers": {
    "coolbeans-library": {
      "command": "php",
      "args": ["artisan", "mcp:start", "test"],
      "cwd": "/absolute/path/to/laravel-mcp-test-v1"
    }
  }
}
```

#### 3. Restart Claude Desktop
After adding the configuration, completely restart Claude Desktop to load your MCP server.

## 🔍 Advanced Search Capabilities

### 🧠 Intelligent Search Modes
The tool automatically detects search intent or accepts explicit mode parameters:

- **`comprehensive`** (default): Searches all fields including content, titles, descriptions, notes, file labels, lecture series names, etc.
- **`name_only`**: Only searches file names, titles, and file labels - triggered by phrases like "file named" or "file called"
- **`content_only`**: Only searches document content and content samples - triggered by "search content" or "inside content"
- **`exclude_content`**: Searches everything except content fields - triggered by "don't look through content" or "exclude content"
- **`partial_match`**: Searches individual words across fields - triggered by "partial match" or "similar to"

### 📄 Content Summary Feature
Get full content access for any file:

**By File Name:**
```
"Please give me a brief summary of Silver File_5147.pdf"
"Get me the content of Prayer Day Ordination Ceremony"
```

**By Database ID:**
```
"Please give me a brief summary of ID 1234"
"Show me the content of file ID 5678"
```

**Smart Content Management:**
- Files ≤2000 chars: Full content displayed
- Files ≤5000 chars: First 1,500 chars + continuation note  
- Large files: Beginning + relevant sections + ending excerpts
- Automatic relevant section detection based on query context

### 📊 Enhanced Search Results
All search results now include:
- **Database ID**: `[ID: 1234]` for easy reference
- **Descriptions**: Truncated descriptions when available
- **Content indicators**: Shows if full content is available and length
- **Metadata**: File numbers, lecture codes, sources, dates
- **Action tips**: Instructions for getting full content summaries

### 🔧 Response Format Options
- **`summary`**: Overview with stats, types, sources, and top 5 results with IDs and descriptions
- **`list`**: Simple numbered list with IDs, descriptions, and content summary tips
- **`detailed`**: Complete metadata for each file including content length and summary instructions
- **`content_summary`**: Full content access for specific files with smart excerpting

### 🎯 Natural Language Processing
The tool understands natural language queries:
- *"books about prayer from coolbeans"* → comprehensive search, source filter
- *"just find a file named Silver_File"* → name_only search mode
- *"don't look through content, find meditation"* → exclude_content mode  
- *"partial match for spiritual development"* → partial_match mode
- *"brief summary of [filename]"* → content_summary mode

### 📋 Database Fields Searched
**Comprehensive mode searches across:**
- `content` - Full document text
- `title` - Document titles  
- `description` - File descriptions
- `name` - File names
- `content_sample` - Content previews
- `note` - Additional notes
- `file_label` - File labels
- `alternate_title_per_the_transcript` - Alternative titles
- `lecture_series_original_names` - Lecture series information
- `mimeo_titles_not_in_tech_vols` - Technical volume titles
- `lrh_article` - Article references
- `file_number` - File numbering system
- `lecture_code` - Lecture coding system

## 💬 Usage Examples

### 🔍 **Search Examples**

**Basic Searches:**
```
"Please give me list of books related to praying on coolbeans"
"Find me lectures about meditation and spiritual development"  
"Show me detailed information about documents related to philosophy"
```

**Smart Search Modes:**
```
"Just find a file named Silver_File_5147"  → name_only mode
"Don't look through content, find files about prayer"  → exclude_content mode  
"Partial match for spiritual development concepts"  → partial_match mode
"Search content only for meditation techniques"  → content_only mode
```

**📄 Content Summary Requests:**
```
"Please give me a brief summary of Silver File_5147.pdf"
"Get me the content of Prayer Day Ordination Ceremony"
"Show me the full content of ID 1234"
"Brief summary of the book about meditation from file 5678"
```

**🎯 Advanced Filtering:**
```
"Find books from coolbeans about prayer, show detailed results"
"List audio files from any source about personal growth"  
"Get comprehensive search for documents excluding content about philosophy"
```

### 🖥️ **Web Interface Usage:**
- Query: "prayer meditation spiritual"
- Source: "coolbeans"  
- Type: "book"
- Response Type: "summary"
- Search Mode: "comprehensive" (auto-detected)

### 🔌 **API Usage Examples:**

**Basic Search:**
```bash
curl -X POST http://localhost:8000/api/search \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  -d '{
    "query": "books about prayer from coolbeans",
    "source": "coolbeans",
    "type": "book", 
    "response_type": "summary",
    "limit": 10
  }'
```

**Content Summary Request:**
```bash
curl -X POST http://localhost:8000/api/search \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  -d '{
    "query": "brief summary of Silver File_5147.pdf",
    "response_type": "content_summary"
  }'
```

**Advanced Search with Modes:**
```bash
curl -X POST http://localhost:8000/api/search \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  -d '{
    "query": "meditation techniques",
    "search_mode": "content_only",
    "response_type": "detailed",
    "limit": 5
  }'
```

### 🔄 **Typical Workflow:**

1. **Initial Search**: `"Please give me list of books related to praying on coolbeans"`
   - Returns: List with IDs, descriptions, and content indicators

2. **Content Access**: `"Please give me a brief summary of Silver File_5147.pdf"`  
   - Returns: Full content summary with smart excerpting

3. **Follow-up**: Use IDs from search results for direct access
   - `"Please give me a brief summary of ID 1234"`

## 📊 Request Tracking

Every search request is automatically tracked with:
- **Request Details**: Query text, parameters, timestamp
- **Response Data**: Generated response text and found files
- **Performance Metrics**: Processing time and result counts
- **User Information**: IP address and browser details
- **Status Tracking**: Success, failure, or error states

Access request history at `http://localhost:8000/mcp/requests`

## 🧰 Available API Endpoints

### Web Routes
- `GET /` - Main search interface
- `GET /mcp/requests` - Request history
- `GET /mcp/requests/{id}` - Request details
- `POST /mcp/search` - Perform search

### API Routes
- `POST /api/search` - Programmatic search endpoint

## 📁 Project Structure

```
app/
├── Http/Controllers/
│   └── McpController.php           # Web interface controller
├── Mcp/
│   ├── Servers/
│   │   └── TestMCPServer.php       # MCP server configuration
│   └── Tools/
│       └── CoolbeansLibrarySearchTool.php      # CoolBeans Library Search tool
├── Models/
│   ├── File.php                    # Files table model
│   └── McpRequest.php              # Request tracking model
└── ...

resources/views/
├── layouts/
│   └── app.blade.php               # Main layout
└── mcp/
    ├── index.blade.php             # Search interface
    ├── requests.blade.php          # Request history
    └── request-details.blade.php   # Request details

database/migrations/
└── *_create_mcp_requests_table.php # Request tracking table
```

## 🔧 Database Schema

### Files Table (Existing)
Your existing `files` table should contain fields like:
- `content` - Full text content
- `title` - Document title
- `description` - Document description
- `name` - File name
- `type` - File type
- `source` - Source identifier
- `index_status` - Indexing status (3 = indexed)
- And many other metadata fields

### MCP Requests Table (New)
Created by migration:
- `id` - Primary key
- `session_id` - User session identifier
- `request_text` - Search query
- `request_type` - Type of request
- `search_parameters` - JSON search parameters
- `response_text` - Generated response
- `found_files` - JSON array of found files
- `files_count` - Number of files found
- `status` - Request status
- `processing_time` - Response time
- `error_message` - Error details if any
- `user_ip` - Client IP address
- `user_agent` - Browser information
- `created_at/updated_at` - Timestamps

## 🐛 Troubleshooting

### Common Issues

**MCP Server Not Starting:**
```bash
# Verify server registration
php artisan mcp:start --help
```

**Database Connection Issues:**
```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();
```

**Search Not Working:**
```bash
# Check if files are indexed
php artisan tinker
>>> App\Models\File::where('index_status', 3)->count();
```

**Permission Errors:**
```bash
# Fix storage permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### Logs and Debugging
- Laravel logs: `storage/logs/laravel.log`
- Claude Desktop logs: `~/Library/Logs/Claude/mcp*.log`
- Web server logs: Check your web server error logs

## 🧪 Testing

### Test the Search Functionality
```bash
# Via web interface
curl -X POST http://localhost:8000/mcp/search \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: $(php artisan tinker --execute="echo csrf_token();")" \
  -d '{"query": "test search"}'

# Check request history
curl http://localhost:8000/mcp/requests
```

### Test MCP Integration
1. Configure Claude Desktop with the MCP server
2. Ask Claude: "Search for books about prayer from coolbeans"
3. Check the web interface for request history

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🔗 Related Resources

- [Laravel MCP Package Documentation](https://github.com/laravel/mcp)
- [Model Context Protocol Specification](https://modelcontextprotocol.io/)
- [Claude Desktop MCP Integration](https://docs.anthropic.com/claude/docs)
- [Laravel Framework Documentation](https://laravel.com/docs)

---

**Built with ❤️ for the CoolBeans Library using Laravel and the Model Context Protocol**

## 🚀 Latest Enhancements (v2.0)

### 🆕 **Content Summary Feature**
- **Full Content Access**: Get complete document content by name or database ID
- **Smart Excerpting**: Automatic content management for large files (beginning + relevant sections + ending)
- **Context-Aware**: Finds relevant sections based on your query
- **Multiple Access Methods**: By filename, ID, or natural language request

### 🧠 **Intelligent Search Modes**
- **Auto-Detection**: Automatically determines search intent from natural language
- **5 Search Modes**: Comprehensive, name-only, content-only, exclude-content, partial-match
- **Edge Case Handling**: Handles phrases like "don't look through content", "file named X", "partial match"
- **15+ Database Fields**: Searches across all relevant metadata and content fields

### 📊 **Enhanced Results Display**
- **Database IDs**: All results show `[ID: 1234]` for easy reference  
- **Rich Metadata**: Descriptions, content length, file numbers, lecture codes
- **Actionable Tips**: Instructions for accessing full content summaries
- **Content Indicators**: Shows which files have full content available

### 🔄 **Improved Workflow**
1. **Search** → Get list with IDs and descriptions
2. **Content Access** → Request full content by name/ID  
3. **Follow-up** → Use IDs for direct access to specific files

## 🎯 Key Benefits

- **🧠 Intelligent Search**: Natural language queries with automatic mode detection
- **📄 Full Content Access**: Complete document content with smart excerpting
- **🔍 Comprehensive Coverage**: Searches across 15+ database fields and metadata
- **🎛️ Multiple Interfaces**: Web UI, API, and MCP protocol support  
- **📊 Enhanced Results**: IDs, descriptions, content indicators, and actionable tips
- **⚡ Performance Monitoring**: Track search performance and content access patterns
- **📋 Complete Audit Trail**: Full history of searches and content access
- **📤 Export Capabilities**: Download results and content summaries
- **📱 Mobile Ready**: Responsive design works on all devices
- **🎯 Edge Case Handling**: Robust natural language processing and query interpretation