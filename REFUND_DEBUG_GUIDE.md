# Refund Submission Error: "Invalid request method"

## Issue
The error "Invalid request method" means the form is not being submitted as POST to submit-refund.php.

## Possible Causes

### 1. JavaScript Form Submission Issue
The JavaScript in profile.php uses FormData which should work, but check:
- Browser console for any JavaScript errors
- Network tab to see if the request is actually being sent
- Check if the request method shows as POST or GET

### 2. Form Data Not Being Sent
The FormData might not be properly constructed.

## Quick Fix

Replace the `submitRefund` function in profile.php with this version:

```javascript
function submitRefund(event, appId) {
    event.preventDefault();
    const form = event.target;
    
    // Get form values
    const refundAmount = form.querySelector('[name="refund_amount"]').value;
    const reason = form.querySelector('[name="reason"]').value;
    const bankName = form.querySelector('[name="bank_name"]').value;
    const accountNumber = form.querySelector('[name="account_number"]').value;
    const accountName = form.querySelector('[name="account_name"]').value;
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class=\"fas fa-spinner fa-spin\"></i> Submitting...';
    submitBtn.disabled = true;
    
    // Create FormData and explicitly append each field
    const formData = new FormData();
    formData.append('application_id', appId);
    formData.append('refund_amount', refundAmount);
    formData.append('reason', reason);
    formData.append('bank_name', bankName);
    formData.append('account_number', accountNumber);
    formData.append('account_name', accountName);
    
    fetch('submit-refund.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast('Refund request submitted successfully', 'success');
            closeModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error submitting refund request', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}
```

## Testing Steps

1. Open browser Developer Tools (F12)
2. Go to Network tab
3. Try to submit a refund request
4. Check the request details:
   - Method should be POST
   - Form Data tab should show all fields
   - Response should show the JSON result

## Alternative: Check if it's a CORS or Server Issue

If the above doesn't work, the issue might be:
- Apache/PHP configuration blocking POST requests
- .htaccess rewrite rules interfering
- PHP session issues

Try accessing submit-refund.php directly in browser - you should see:
```json
{"success":false,"message":"Invalid request method"}
```

If you see a different error, that's the real issue.
