@extends('layouts.app')

@section('title', 'Privacy Policy')

@section('content')
<div class="wrapper ovh bgc-gmart-gray">
    @php
   $categoriesWithSubs = App\Models\Category::select('id', 'name', 'slug', 'image')
                    ->with(['subcategories' => fn($q) => 
                        $q->select('id', 'category_id', 'name', 'slug')
                          ->orderBy('sort_order')
                          ->limit(10)
                    ])
                    ->limit(10)
                    ->get();
  @endphp
  @include('partials.header')
  @include('partials.navigation', ['categoriesWithSubs' => $categoriesWithSubs ?? []])
  
  <div class="body_content_wrapper position-relative">
    
    <!-- Breadcrumb -->
    <section class="breadcumb-section pt30 pb30">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="breadcumb-style1">
              <div class="breadcumb-list">
                <a href="{{ route('home') }}">Home</a>
                <a href="#">Privacy Policy</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Privacy Policy Content -->
    <section class="our-terms bgc-white pb90">
      <div class="container">
        <div class="row">
          <div class="col-lg-8 offset-lg-2">
            <div class="main-title text-center mb40">
              <h2>Privacy Policy</h2>
              <p class="text-muted">Last updated: {{ date('F d, Y') }}</p>
            </div>
            
            <div class="terms_condition_grid">
              
              <div class="grids mb40">
                <h4 class="mb20">1. Introduction</h4>
                <p class="mb15">Welcome to GenesisHub. We respect your privacy and are committed to protecting your personal data. This privacy policy will inform you about how we handle your personal data when you visit our website and tell you about your privacy rights.</p>
                <p class="mb15">GenesisHub ("we," "us," or "our") operates the e-commerce platform accessible at genesishub.com. This page informs you of our policies regarding the collection, use, and disclosure of personal data when you use our service.</p>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">2. Information We Collect</h4>
                <p class="mb15">We collect several different types of information for various purposes to provide and improve our service to you:</p>
                
                <h5 class="mb15">2.1 Personal Data</h5>
                <p class="mb10">While using our service, we may ask you to provide us with certain personally identifiable information that can be used to contact or identify you, including but not limited to:</p>
                <ul class="mb20">
                  <li>Email address</li>
                  <li>First name and last name</li>
                  <li>Phone number</li>
                  <li>Address, State, Province, ZIP/Postal code, City</li>
                  <li>Cookies and usage data</li>
                </ul>
                
                <h5 class="mb15">2.2 Usage Data</h5>
                <p class="mb15">We may also collect information on how the service is accessed and used. This usage data may include information such as your computer's Internet Protocol address (IP address), browser type, browser version, the pages of our service that you visit, the time and date of your visit, the time spent on those pages, unique device identifiers, and other diagnostic data.</p>
                
                <h5 class="mb15">2.3 Tracking & Cookies Data</h5>
                <p class="mb15">We use cookies and similar tracking technologies to track the activity on our service and hold certain information. You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent.</p>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">3. How We Use Your Information</h4>
                <p class="mb15">GenesisHub uses the collected data for various purposes:</p>
                <ul class="mb20">
                  <li>To provide and maintain our service</li>
                  <li>To notify you about changes to our service</li>
                  <li>To allow you to participate in interactive features when you choose to do so</li>
                  <li>To provide customer support</li>
                  <li>To gather analysis or valuable information so that we can improve our service</li>
                  <li>To monitor the usage of our service</li>
                  <li>To detect, prevent and address technical issues</li>
                  <li>To process your transactions and manage your orders</li>
                  <li>To send you marketing and promotional communications (you can opt-out at any time)</li>
                  <li>To personalize your shopping experience</li>
                </ul>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">4. Data Sharing and Disclosure</h4>
                <p class="mb15">We may share your personal information in the following situations:</p>
                
                <h5 class="mb15">4.1 With Service Providers</h5>
                <p class="mb15">We may share your personal information with third-party service providers to monitor and analyze the use of our service, to process payments, to provide customer support, and to assist us in marketing our products and services.</p>
                
                <h5 class="mb15">4.2 With Sellers</h5>
                <p class="mb15">When you make a purchase, we share necessary information with the seller to fulfill your order, including your name, shipping address, and contact information.</p>
                
                <h5 class="mb15">4.3 For Legal Reasons</h5>
                <p class="mb15">We may disclose your personal data if required to do so by law or in response to valid requests by public authorities (e.g., a court or government agency).</p>
                
                <h5 class="mb15">4.4 Business Transfers</h5>
                <p class="mb15">If we are involved in a merger, acquisition, or asset sale, your personal data may be transferred. We will provide notice before your personal data is transferred and becomes subject to a different privacy policy.</p>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">5. Data Security</h4>
                <p class="mb15">The security of your data is important to us. We implement appropriate technical and organizational measures to protect your personal data against unauthorized or unlawful processing, accidental loss, destruction, or damage.</p>
                <p class="mb15">However, please remember that no method of transmission over the Internet or method of electronic storage is 100% secure. While we strive to use commercially acceptable means to protect your personal data, we cannot guarantee its absolute security.</p>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">6. Your Data Protection Rights</h4>
                <p class="mb15">Depending on your location, you may have the following rights regarding your personal data:</p>
                <ul class="mb20">
                  <li><strong>The right to access</strong> – You have the right to request copies of your personal data.</li>
                  <li><strong>The right to rectification</strong> – You have the right to request that we correct any information you believe is inaccurate or complete information you believe is incomplete.</li>
                  <li><strong>The right to erasure</strong> – You have the right to request that we erase your personal data, under certain conditions.</li>
                  <li><strong>The right to restrict processing</strong> – You have the right to request that we restrict the processing of your personal data, under certain conditions.</li>
                  <li><strong>The right to object to processing</strong> – You have the right to object to our processing of your personal data, under certain conditions.</li>
                  <li><strong>The right to data portability</strong> – You have the right to request that we transfer the data that we have collected to another organization, or directly to you, under certain conditions.</li>
                </ul>
                <p class="mb15">To exercise any of these rights, please contact us at support@genesishub.com</p>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">7. Children's Privacy</h4>
                <p class="mb15">Our service does not address anyone under the age of 13. We do not knowingly collect personally identifiable information from anyone under the age of 13. If you are a parent or guardian and you are aware that your child has provided us with personal data, please contact us.</p>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">8. Cookies Policy</h4>
                <p class="mb15">We use cookies and similar tracking technologies to track activity on our service. You can manage your cookie preferences through your browser settings. Types of cookies we use:</p>
                <ul class="mb20">
                  <li><strong>Essential Cookies:</strong> Necessary for the website to function properly</li>
                  <li><strong>Analytics Cookies:</strong> Help us understand how visitors interact with our website</li>
                  <li><strong>Marketing Cookies:</strong> Used to track visitors across websites to display relevant advertisements</li>
                  <li><strong>Preference Cookies:</strong> Enable the website to remember your preferences</li>
                </ul>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">9. Third-Party Links</h4>
                <p class="mb15">Our service may contain links to other websites that are not operated by us. If you click on a third-party link, you will be directed to that third party's site. We strongly advise you to review the privacy policy of every site you visit.</p>
                <p class="mb15">We have no control over and assume no responsibility for the content, privacy policies, or practices of any third-party sites or services.</p>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">10. Changes to This Privacy Policy</h4>
                <p class="mb15">We may update our privacy policy from time to time. We will notify you of any changes by posting the new privacy policy on this page and updating the "Last updated" date at the top of this privacy policy.</p>
                <p class="mb15">You are advised to review this privacy policy periodically for any changes. Changes to this privacy policy are effective when they are posted on this page.</p>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">11. Contact Us</h4>
                <p class="mb15">If you have any questions about this privacy policy, please contact us:</p>
                <ul class="mb20">
                  <li>By email: support@genesishub.com</li>
                  <li>By visiting our contact page: <a href="{{ route('contact') }}">{{ route('contact') }}</a></li>
                  <li>By phone: +(1) 123 456 7890</li>
                </ul>
              </div>
              
            </div>
          </div>
        </div>
      </div>
    </section>
    
    @include('partials.footer')
    <a class="scrollToHome" href="#"><i class="fas fa-angle-up"></i></a>
  </div>
</div>
@endsection