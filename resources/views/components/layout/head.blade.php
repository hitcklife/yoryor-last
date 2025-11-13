<!-- Open Graph Meta Tags -->
<meta property="og:title" content="{{ $title ?? 'YorYor - Find Your Perfect Match' }}">
<meta property="og:description" content="{{ $description ?? 'Join YorYor, the modern dating app that helps you find meaningful connections.' }}">
<meta property="og:image" content="{{ asset('assets/images/yoryor-og-image.jpg') }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:type" content="website">

<!-- Twitter Card Meta Tags -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title ?? 'YorYor - Find Your Perfect Match' }}">
<meta name="twitter:description" content="{{ $description ?? 'Join YorYor, the modern dating app that helps you find meaningful connections.' }}">
<meta name="twitter:image" content="{{ asset('assets/images/yoryor-og-image.jpg') }}">

<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

<!-- Additional Meta Tags -->
<meta name="robots" content="index, follow">
<meta name="author" content="YorYor">
<link rel="canonical" href="{{ url()->current() }}">