<!DOCTYPE html>
<html>
<head>
    <title>Image-hosting-website.com</title>
</head>
<body>
    <p>Hello {{$details['from']}} shared the image with you</p>
    <p>See the image by clicking here</p>
    <a href="{{ $details['image_link']}}">Click here</a>
    <p>Thank you</p>
</body>
</html>
