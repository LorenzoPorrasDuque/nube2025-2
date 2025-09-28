FROM python:3.9-slim

WORKDIR /app

# Install dependencies
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Copy application
COPY comments_api.py .

# Expose port
EXPOSE 8000

# Run the application
CMD ["uvicorn", "comments_api:app", "--host", "0.0.0.0", "--port", "8000"]