<!DOCTYPE html>
<html>
<head>
    <title>New Customer Inquiry</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6;">
    <h2 style="color: #057A55;">New Contact Form Submission</h2>

    <p>A user submitted an inquiry via the Contact Us form:</p>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td style="padding: 8px; border: 1px solid #ccc; font-weight: bold; width: 120px;">Name:</td>
            <td style="padding: 8px; border: 1px solid #ccc;">{{ $formData['name'] }}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ccc; font-weight: bold;">Email:</td>
            <td style="padding: 8px; border: 1px solid #ccc;">{{ $formData['email'] }}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ccc; font-weight: bold;">Subject:</td>
            <td style="padding: 8px; border: 1px solid #ccc;">{{ $formData['subject'] }}</td>
        </tr>
    </table>

    <h3 style="color: #333;">Message:</h3>
    <p style="padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9; white-space: pre-wrap;">{{ $formData['message'] }}</p>

    <p style="margin-top: 30px; font-size: 0.9em; color: #777;">
        This is an automated notification. Please reply to the customer's email address above.
    </p>
</body>
</html>
