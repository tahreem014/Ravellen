@extends('layouts.policy')
@section('title', 'Privacy Policy')
@section('policy-title', 'Privacy Policy')
@section('policy-tagline', 'How we collect, use, and protect your personal information when you visit or shop at Ravellen.')
@section('effective-date', '[Insert Date]')

@section('policy-body')
<p class="policy-lead">Welcome to Ravellen! We’re a little shop full of surprises, cute stationery, adorable collectibles, BTS merchandise, ceramic items, and mystery gifts.</p>
<p>Your privacy matters to us just as much as the excitement of opening your mystery scoop. This Privacy Policy explains how we collect, use, and protect your personal information when you visit our website or place an order with us.</p>

<h2>1. Information We Collect</h2>
<p>When you visit or shop at Ravellen, we may collect the following information:</p>
<ul>
    <li><strong>Personal Information:</strong> Your name, email address, phone number, and other information you provide when contacting us or placing an order.</li>
    <li><strong>Order Information:</strong> Details about the products you purchase, including mystery scoops, mystery balls, stationery, merchandise, and other items.</li>
    <li><strong>Shipping Information:</strong> Your delivery address and any information necessary to deliver your order.</li>
    <li><strong>Payment Information:</strong> Payment-related details required to process your purchases. Payment information may be handled by third-party payment providers.</li>
    <li><strong>Technical Information:</strong> Your IP address, browser type, device information, and website activity, where applicable.</li>
</ul>
<p>We collect only the information reasonably necessary to operate our shop and provide our services.</p>

<h2>2. How We Use Your Information</h2>
<p>We use your information to:</p>
<ul>
    <li>Process and confirm your orders.</li>
    <li>Prepare and deliver your mystery scoops, mystery balls, and other purchases.</li>
    <li>Communicate with you about your orders, deliveries, or inquiries.</li>
    <li>Provide customer support.</li>
    <li>Improve our website, products, and shopping experience.</li>
    <li>Prevent fraudulent transactions and protect our business.</li>
    <li>Send promotional emails or special offers, where you have consented or where permitted by law.</li>
</ul>
<p>We want your shopping experience to be enjoyable, secure, and as smooth as possible.</p>

<h2>3. Mystery Scoops and Product Information</h2>
<p>Ravellen offers products in unique mystery formats, including mystery scoops and mystery balls.</p>
<p>A scoop may contain different quantities of items depending on its size, while each mystery ball contains one item. The specific contents may vary according to the product purchased.</p>
<p>We use order information to ensure that your purchase is prepared according to the product and scoop size you selected.</p>
<p>Your personal information is never used to determine or influence the mystery contents of your purchase unless a specific customization feature is introduced and explained to you.</p>

<h2>4. Cookies and Website Technologies</h2>
<p>Our website may use cookies and similar technologies to improve your shopping experience.</p>
<p>These technologies may help us:</p>
<ul>
    <li>Remember your shopping cart.</li>
    <li>Maintain website functionality.</li>
    <li>Understand how visitors use our website.</li>
    <li>Improve website performance.</li>
    <li>Support advertising and marketing activities, where applicable.</li>
</ul>
<p>You can manage cookies through your browser settings. Disabling certain cookies may affect some website features.</p>
<p>Where required by law, we will request your consent before using non-essential cookies.</p>

<h2>5. Payments and Third-Party Services</h2>
<p>Ravellen may use third-party services for payment processing, website hosting, order management, shipping, analytics, or other business operations.</p>
<p>These providers may receive information necessary to perform their services.</p>
<p>We do not directly store complete payment card details unless our payment system specifically supports and requires such processing.</p>
<p>Third-party services operate under their own privacy policies and security practices.</p>

<h2>6. Shipping and Delivery</h2>
<p>To deliver your purchases, we may share necessary information, such as your name, phone number, and delivery address, with courier companies or shipping partners.</p>
<p>This information is shared only to the extent reasonably necessary to fulfill your order.</p>
<p>We do not authorize shipping partners to use your information for unrelated purposes.</p>
<p>Delivery times, charges, and conditions are explained in our <a href="{{ route('shipping') }}">Shipping &amp; Returns Policy</a>.</p>

<h2>7. Sharing Your Information</h2>
<p>We respect your privacy and do not sell your personal information.</p>
<p>We may share information with:</p>
<ul>
    <li>Payment processors to complete transactions.</li>
    <li>Courier companies to deliver your orders.</li>
    <li>Website hosting and technical service providers.</li>
    <li>Customer support or order management services.</li>
    <li>Legal authorities when required by applicable law.</li>
</ul>
<p>We take reasonable steps to ensure that information shared with service providers is handled appropriately.</p>

<h2>8. Data Security</h2>
<p>We take reasonable measures to protect your personal information against unauthorized access, loss, misuse, alteration, or disclosure.</p>
<p>However, no online platform or electronic transmission method can guarantee complete security.</p>
<p>We encourage customers to protect their account credentials and avoid sharing sensitive information through unsecured channels.</p>

<h2>9. Your Privacy Rights</h2>
<p>Depending on your location and applicable privacy laws, you may have the right to:</p>
<ul>
    <li>Request access to your personal information.</li>
    <li>Correct inaccurate information.</li>
    <li>Request deletion of your personal information.</li>
    <li>Withdraw consent for certain data processing activities.</li>
    <li>Opt out of promotional emails.</li>
</ul>
<p>You can contact us to exercise any applicable privacy rights.</p>

<h2>10. Data Retention</h2>
<p>We retain personal information only for as long as reasonably necessary to process orders, provide customer support, maintain business records, and meet legal or regulatory requirements.</p>
<p>When information is no longer needed, we will take reasonable steps to delete it or securely dispose of it, where appropriate.</p>

<h2>11. Children's Privacy</h2>
<p>Ravellen is not intended to collect personal information from children in violation of applicable privacy laws.</p>
<p>If you believe a child has provided personal information to us without appropriate authorization, please contact us so we can review and address the situation.</p>

<h2>12. External Links</h2>
<p>Our website may contain links to third-party websites, including social media platforms and payment services.</p>
<p>We are not responsible for the privacy practices or content of external websites.</p>
<p>We recommend reviewing their privacy policies before sharing personal information.</p>

<h2>13. Changes to This Privacy Policy</h2>
<p>We may update this Privacy Policy from time to time to reflect changes in our business, website features, or applicable legal requirements.</p>
<p>Any changes will be published on this page with an updated effective date.</p>
<p>We encourage you to check this page occasionally to stay informed.</p>

<h2>14. Contact Us</h2>
<p>Have questions about your privacy or how your information is handled? We'd love to hear from you!</p>
@include('pages.partials.policy-contact', ['fields' => ['name', 'email', 'website', 'instagram']])

<div class="policy-closing">
    <p><strong>Thank you for shopping with Ravellen and being part of our little world of surprises! ♡</strong></p>
    <p><em>Every scoop holds a surprise, but your privacy should never be one.</em></p>
</div>
@endsection
