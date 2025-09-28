from fastapi import FastAPI, HTTPException, Request
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from pydantic import BaseModel
import mysql.connector
from mysql.connector import Error
import os
from typing import List

app = FastAPI()

# Security middleware to block direct access
@app.middleware("http")
async def security_middleware(request: Request, call_next):
    """Block direct browser access - allow API Gateway traffic"""
    user_agent = request.headers.get("user-agent", "")
    x_forwarded_for = request.headers.get("x-forwarded-for", "")
    x_amzn_trace_id = request.headers.get("x-amzn-trace-id", "")
    
    # Allow if request comes through AWS API Gateway (has AWS headers)
    if x_amzn_trace_id or "AmazonAPIGateway" in user_agent or "AWS" in user_agent:
        response = await call_next(request)
        return response
    
    # Allow if it's a server-to-server request (no browser user-agent)
    if not user_agent or "Mozilla" not in user_agent:
        response = await call_next(request)
        return response
    
    # Block direct browser access
    return JSONResponse(
        status_code=403,
        content={"error": "Direct access not allowed - please use the official website"}
    )

# Get allowed origins from environment variable
# For production: set API_GATEWAY_URL to your API Gateway domain
# For development: keep as localhost


# CORS configuration - restrict to CloudFront domain AND API Gateway
app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "https://d33dat30aefnzp.cloudfront.net",  # Your frontend
        "https://uf4ggsywu3.execute-api.us-east-1.amazonaws.com"  # API Gateway only
    ],
    allow_credentials=False,
    allow_methods=["GET", "POST", "OPTIONS"],
    allow_headers=["*"],
)

# Custom CORS response wrapper
def cors_response(data, status_code: int = 200):
    """Wrapper to ensure every response has CORS headers"""
    response = JSONResponse(
        content=data,
        status_code=status_code,
        headers={
            "Access-Control-Allow-Origin": "https://d33dat30aefnzp.cloudfront.net",
            "Access-Control-Allow-Methods": "GET, POST, OPTIONS",
            "Access-Control-Allow-Headers": "*",
            "Access-Control-Max-Age": "86400"
        }
    )
    return response

# Database configuration
DB_CONFIG = {
    'host': os.getenv('DB_HOST', 'database.c4r4g0w6srxg.us-east-1.rds.amazonaws.com'),
    'database': os.getenv('DB_NAME', 'prueba'),
    'user': os.getenv('DB_USER', 'admin'),
    'password': os.getenv('DB_PASSWORD', '12345678'),
    'charset': 'utf8mb4'
}

# Pydantic models
class CommentCreate(BaseModel):
    product_name: str  # Changed to use product_name instead of product_id
    name: str
    comment: str

class Comment(BaseModel):
    id: int
    product_id: int
    product_name: str  # Added product_name to response
    name: str
    comment: str
    created_at: str

def get_db_connection():
    try:
        connection = mysql.connector.connect(**DB_CONFIG)
        return connection
    except Error as e:
        raise HTTPException(status_code=500, detail=f"Database connection failed: {str(e)}")

def get_or_create_product(product_name: str, connection):
    """Get product ID by name, create if doesn't exist"""
    cursor = connection.cursor(dictionary=True)
    
    try:
        # Try to find existing product
        cursor.execute("SELECT product_id FROM products WHERE product_name = %s", (product_name,))
        result = cursor.fetchone()
        
        if result:
            return result['product_id']
        
        # Product doesn't exist, create it
        cursor.execute(
            "INSERT INTO products (product_name, product_description) VALUES (%s, %s)",
            (product_name, f"Auto-created product: {product_name}")
        )
        connection.commit()
        return cursor.lastrowid
        
    except Error as e:
        connection.rollback()
        raise HTTPException(status_code=500, detail=f"Failed to get or create product: {str(e)}")
    finally:
        cursor.close()

@app.get("/comments/by-name/{product_name}", response_model=List[Comment])
async def get_comments_by_name(product_name: str):
    """Get all comments for a product by name"""
    connection = get_db_connection()
    cursor = connection.cursor(dictionary=True)
    
    try:
        cursor.execute("""
            SELECT c.id, c.product_id, p.product_name, c.name, c.comment, c.created_at 
            FROM comments c 
            JOIN products p ON c.product_id = p.product_id 
            WHERE p.product_name = %s 
            ORDER BY c.created_at DESC
        """, (product_name,))
        comments = cursor.fetchall()
        
        # Convert datetime to string
        for comment in comments:
            comment['created_at'] = str(comment['created_at'])
            
        return cors_response(comments)
    except Error as e:
        raise HTTPException(status_code=500, detail=f"Failed to fetch comments: {str(e)}")
    finally:
        cursor.close()
        connection.close()

@app.options("/comments")
async def options_comments():
    """Handle preflight CORS requests for comments endpoint"""
    return cors_response({"message": "OK"})

@app.post("/comments", response_model=dict)
async def create_comment(comment: CommentCreate):
    """Create a new comment for a product (auto-creates product if doesn't exist)"""
    connection = get_db_connection()
    
    try:
        # Get or create product
        product_id = get_or_create_product(comment.product_name, connection)
        
        # Insert comment
        cursor = connection.cursor()
        cursor.execute(
            "INSERT INTO comments (product_id, name, comment) VALUES (%s, %s, %s)",
            (product_id, comment.name, comment.comment)
        )
        connection.commit()
        
        result = {"message": "Comment created successfully", "id": cursor.lastrowid, "product_id": product_id}
        return cors_response(result)
    except Error as e:
        connection.rollback()
        raise HTTPException(status_code=500, detail=f"Failed to create comment: {str(e)}")
    finally:
        if 'cursor' in locals():
            cursor.close()
        connection.close()

@app.get("/")
async def root():
    """Public health check endpoint"""
    return cors_response({
        "message": "Comments API v2.5 - Direct Access Blocked", 
        "status": "healthy",
        "endpoints": ["/comments (POST)", "/comments/by-name/{product_name} (GET)"]
    })

@app.get("/health")
async def health_check():
    """Health check for OpenShift/Kubernetes"""
    import time
    return cors_response({
        "status": "healthy", 
        "timestamp": int(time.time()),
        "version": "2.6.0"
    })

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)