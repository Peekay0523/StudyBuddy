<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Career API</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 8px; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
        button { padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        button:hover { background: #5568d3; }
    </style>
</head>
<body>
    <h1>Career API Test Page</h1>
    
    <div class="test-section">
        <h2>1. Test Categories API</h2>
        <button onclick="testCategories()">Test /api/career-categories</button>
        <div id="categories-result"></div>
    </div>
    
    <div class="test-section">
        <h2>2. Test Search API (with AI Fallback)</h2>
        <button onclick="testSearch('doctor')">Search "doctor"</button>
        <button onclick="testSearch('engineer')">Search "engineer"</button>
        <button onclick="testSearch('mechanical engineering')">Search "mechanical engineering" (AI)</button>
        <button onclick="testSearch('data scientist')">Search "data scientist" (AI)</button>
        <button onclick="testSearch('renewable energy')">Search "renewable energy" (AI)</button>
        <div id="search-result"></div>
    </div>
    
    <div class="test-section">
        <h2>3. Test Institutions API</h2>
        <button onclick="testInstitutions()">Test /api/institutions</button>
        <div id="institutions-result"></div>
    </div>
    
    <div class="test-section">
        <h2>4. Manual Test</h2>
        <input type="text" id="manual-url" placeholder="Enter API URL" style="width: 300px; padding: 8px;" value="/api/search-careers?q=doctor">
        <button onclick="testManual()">Test</button>
        <div id="manual-result"></div>
    </div>
    
    <hr>
    <p><a href="/upload-report-card">Go to Upload Report Card Page</a></p>
    <p><a href="/direct-career-api?action=categories">Direct API: Categories</a> | <a href="/direct-career-api?action=search&q=doctor">Direct API: Search Doctor</a></p>
    
    <script>
        async function testCategories() {
            const resultDiv = document.getElementById('categories-result');
            resultDiv.innerHTML = '<p>Loading...</p>';
            
            try {
                // Try direct API first
                console.log('Testing direct API...');
                const response = await fetch('/direct-career-api?action=categories');
                console.log('Direct API response status:', response.status);
                console.log('Response content type:', response.headers.get('content-type'));
                
                const text = await response.text();
                console.log('Response text:', text.substring(0, 500));
                
                // Check if it's HTML instead of JSON
                if (text.trim().startsWith('<')) {
                    resultDiv.innerHTML = '<p class="error">✗ Server returned HTML instead of JSON</p>' +
                        '<pre>' + text.substring(0, 1000) + '</pre>';
                    return;
                }
                
                const data = JSON.parse(text);
                console.log('Categories data:', data);
                
                if (data.success) {
                    resultDiv.innerHTML = '<p class="success">✓ Success!</p>' +
                        '<p>Categories: ' + data.categories.join(', ') + '</p>' +
                        '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                } else {
                    resultDiv.innerHTML = '<p class="error">✗ Error: ' + (data.error || 'Unknown error') + '</p>' +
                        '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                }
            } catch (error) {
                resultDiv.innerHTML = '<p class="error">✗ Exception: ' + error.message + '</p>' +
                    '<pre>' + error.stack + '</pre>';
            }
        }
        
        async function testSearch(query) {
            const resultDiv = document.getElementById('search-result');
            resultDiv.innerHTML = '<p>Searching for "' + query + '"...</p><p style="font-size: 12px; color: #64748b;">This may take a few seconds if using AI...</p>';
            
            try {
                const response = await fetch('/api/search-careers?q=' + encodeURIComponent(query));
                const text = await response.text();
                console.log('Search response text:', text.substring(0, 500));
                
                if (text.trim().startsWith('<')) {
                    resultDiv.innerHTML = '<p class="error">✗ Server returned HTML instead of JSON</p>' +
                        '<pre>' + text.substring(0, 1000) + '</pre>';
                    return;
                }
                
                const data = JSON.parse(text);
                console.log('Search data:', data);
                
                if (data.success) {
                    let html = '<p class="success">✓ Success! Found ' + data.count + ' careers</p>';
                    if (data.from_ai) {
                        html += '<div style="padding: 10px; background: #f0f9ff; border-left: 3px solid #0284c7; margin: 10px 0;">' +
                            '<i class="fas fa-robot"></i> <strong>AI-Powered Results</strong> - Generated by AI' +
                            '</div>';
                    }
                    if (data.careers && data.careers.length > 0) {
                        html += '<ul>';
                        data.careers.forEach(career => {
                            html += '<li><strong>' + career.name + '</strong> (APS: ' + career.min_aps_score + ') - ' + 
                                (career.institutions ? career.institutions.length : 0) + ' institutions</li>';
                        });
                        html += '</ul>';
                    }
                    resultDiv.innerHTML = html + '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                } else {
                    resultDiv.innerHTML = '<p class="error">✗ Error: ' + (data.error || 'Unknown error') + '</p>' +
                        '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                }
            } catch (error) {
                resultDiv.innerHTML = '<p class="error">✗ Exception: ' + error.message + '</p>';
            }
        }
        
        async function testInstitutions() {
            const resultDiv = document.getElementById('institutions-result');
            resultDiv.innerHTML = '<p>Loading...</p>';
            
            try {
                const response = await fetch('/api/institutions');
                const data = await response.json();
                console.log('Institutions data:', data);
                
                if (data.success) {
                    resultDiv.innerHTML = '<p class="success">✓ Success! Found ' + data.count + ' institutions</p>' +
                        '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                } else {
                    resultDiv.innerHTML = '<p class="error">✗ Error: ' + (data.error || 'Unknown error') + '</p>';
                }
            } catch (error) {
                resultDiv.innerHTML = '<p class="error">✗ Exception: ' + error.message + '</p>';
            }
        }
        
        async function testManual() {
            const url = document.getElementById('manual-url').value;
            const resultDiv = document.getElementById('manual-result');
            resultDiv.innerHTML = '<p>Testing ' + url + '...</p>';
            
            try {
                const response = await fetch(url);
                const data = await response.json();
                console.log('Manual test data:', data);
                
                resultDiv.innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            } catch (error) {
                resultDiv.innerHTML = '<p class="error">✗ Exception: ' + error.message + '</p>';
            }
        }
        
        // Auto-run tests on page load
        window.addEventListener('DOMContentLoaded', () => {
            testCategories();
            testSearch('doctor');
        });
    </script>
</body>
</html>
