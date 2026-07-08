<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Your 15-Day Free Trial POS Account | YUVAVI/POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --primary: #ff4757;
            --primary-dark: #e03d4b;
            --accent: #ff6b81;
            --dark: #1e293b;
            --light: #f8fafc;
            --slate: #64748b;
            --success: #2ed573;
        }

        body {
            font-family: "Outfit", sans-serif;
            background-color: #ffffff;
            color: var(--dark);
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
        }

        .split-container {
            display: flex;
            min-height: 100vh;
        }

        /* Left Sidebar Styling */
        .left-panel {
            flex: 1.1;
            background: linear-gradient(135deg, #1e293b 0%, var(--primary) 120%);
            color: #ffffff;
            padding: 60px 80px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            top: -50px;
            left: -50px;
        }

        .left-panel::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.03);
            bottom: -100px;
            right: -100px;
        }

        .brand-logo {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .brand-logo span {
            color: var(--accent);
        }

        .hero-title {
            font-size: 42px;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 24px;
            letter-spacing: -1px;
        }

        .hero-desc {
            font-size: 16px;
            opacity: 0.85;
            line-height: 1.6;
            margin-bottom: 40px;
            font-weight: 400;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0 0 50px 0;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 18px;
            font-size: 15px;
            font-weight: 500;
        }

        .feature-item i {
            background-color: rgba(255, 255, 255, 0.15);
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 12px;
            flex-shrink: 0;
        }

        .left-footer {
            font-size: 13px;
            opacity: 0.7;
            font-weight: 500;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 20px;
        }

        /* Right Panel Form Styling */
        .right-panel {
            flex: 1;
            padding: 40px 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #fcfcfe;
        }

        .form-container {
            width: 100%;
            max-width: 520px;
        }

        .logo-header {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .logo-header span {
            color: var(--primary);
        }

        .form-title {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-top: 20px;
            margin-bottom: 6px;
        }

        .form-subtitle {
            color: var(--slate);
            font-size: 14px;
            margin-bottom: 30px;
            font-weight: 500;
        }

        .signup-card {
            background-color: #ffffff;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            border: 1px solid rgba(226, 232, 240, 0.8);
            position: relative;
        }

        /* Step Progress Bar */
        .step-progress-wrapper {
            position: relative;
            margin-bottom: 25px;
        }

        .step-progress-bar {
            height: 4px;
            background-color: #e2e8f0;
            border-radius: 2px;
            overflow: hidden;
        }

        .step-progress-fill {
            height: 100%;
            background-color: var(--primary);
            width: 33.33%;
            transition: width 0.4s ease-in-out;
        }

        .step-indicator {
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 20px;
        }

        /* Inputs and Labels */
        .form-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--slate);
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 15px;
            font-weight: 500;
            background-color: #f8fafc;
            color: var(--dark);
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            background-color: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.15);
            outline: none;
        }

        /* Custom Alert Banner */
        .alert-banner {
            background-color: #ebfffc;
            border-left: 4px solid var(--success);
            color: #0d5a4c;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
            display: none;
            align-items: center;
        }

        .alert-banner.show {
            display: flex;
        }

        .subdomain-preview {
            background-color: #f1f2f6;
            border: 1px dashed #ced6e0;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 13px;
            font-weight: 600;
            color: var(--slate);
            margin-top: 15px;
        }

        .subdomain-preview strong {
            color: var(--primary);
        }

        /* Verify Badge */
        .verified-badge {
            color: var(--success);
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 10px;
        }

        /* Buttons */
        .btn-next, .btn-submit {
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 14px 28px;
            font-weight: 700;
            font-size: 15px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(108, 92, 231, 0.25);
        }

        .btn-next:hover, .btn-submit:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(108, 92, 231, 0.35);
            color: white;
        }

        .btn-next:active, .btn-submit:active {
            transform: translateY(0);
        }

        .btn-next:disabled {
            background-color: #cbd5e1;
            box-shadow: none;
            cursor: not-allowed;
            transform: none;
        }

        .btn-back {
            background-color: #f1f2f6;
            color: var(--slate);
            border: none;
            border-radius: 12px;
            padding: 14px 28px;
            font-weight: 700;
            font-size: 15px;
            transition: all 0.2s ease;
        }

        .btn-back:hover {
            background-color: #e4e7eb;
            color: var(--dark);
        }

        .btn-action-small {
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            padding: 10px 18px;
            transition: all 0.2s ease;
        }

        .footer-links {
            margin-top: 25px;
            text-align: center;
            font-size: 12px;
            color: var(--slate);
            font-weight: 500;
        }

        .footer-links a {
            color: var(--slate);
            text-decoration: none;
            margin: 0 8px;
        }

        .footer-links a:hover {
            color: var(--primary);
            text-decoration: underline;
        }

        /* Loading Overlay Styling */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.98);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease-in-out;
        }

        .loading-overlay.show {
            opacity: 1;
            pointer-events: auto;
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 5px solid rgba(108, 92, 231, 0.1);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s infinite linear;
            margin-bottom: 25px;
        }

        .loading-title {
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .loading-subtitle {
            color: var(--slate);
            font-size: 14px;
            font-weight: 500;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Breakpoints */
        @media (max-width: 992px) {
            .split-container {
                flex-direction: column;
            }
            .left-panel {
                padding: 40px;
                min-height: auto;
            }
            .hero-title {
                font-size: 32px;
            }
            .right-panel {
                padding: 40px 20px;
            }
        }
    </style>
</head>
<body>

    <div class="split-container">
        <!-- Left panel -->
        <div class="left-panel">
            <div class="brand-logo d-flex align-items-center">
                <img src="{{ asset('in_shop_app_icon.png') }}" alt="Logo" style="width: 36px; height: 36px; border-radius: 8px; margin-right: 12px; border: 1.5px solid rgba(255,255,255,0.2);">
                In-Shop<span style="font-weight: 400; opacity: 0.9;">/POS</span>
            </div>

            <div class="my-auto py-5">
                <h1 class="hero-title">Launch Your Smarter Store Dashboard</h1>
                <p class="hero-desc">Get access to the most intuitive point-of-sale system built to streamline billing, display orders instantly, and run multi-store operations from a single dashboard.</p>
                
                <ul class="feature-list">
                    <li class="feature-item">
                        <i class="fas fa-bolt"></i> Live Workspace Set Up in 60 Seconds
                    </li>
                    <li class="feature-item">
                        <i class="fas fa-gift"></i> 15-Day Full-Access Free Trial
                    </li>
                    <li class="feature-item">
                        <i class="fas fa-shield-alt"></i> Enterprise-Grade Security & Performance
                    </li>
                    <li class="feature-item">
                        <i class="fas fa-chart-line"></i> Real-Time Analytics & Order Flow
                    </li>
                </ul>
            </div>

            <div class="left-footer">
                <strong>Powering Next-Gen Commerce</strong><br>
                High-performance restaurant & retail operating system.
            </div>
        </div>

        <!-- Right panel -->
        <div class="right-panel">
            <div class="form-container">
                <div class="logo-header d-flex align-items-center mb-3">
                    <img src="{{ asset('in_shop_app_icon.png') }}" alt="Logo" style="width: 44px; height: 44px; border-radius: 10px; margin-right: 12px; box-shadow: 0 4px 12px rgba(255, 71, 87, 0.15);">
                    <div style="line-height: 1; font-weight: 800; font-size: 32px;">In-Shop<span style="font-weight: 400; color: var(--slate);">/POS</span></div>
                </div>
                <h2 class="form-title">Start Your Free Trial</h2>
                <p class="form-subtitle">Set up your workspace and explore the dashboard in seconds.</p>

                <div class="signup-card">
                    <!-- Progress bar -->
                    <div class="step-progress-wrapper">
                        <div class="step-progress-bar">
                            <div class="step-progress-fill" id="progressFill"></div>
                        </div>
                    </div>

                    <div id="stepIndicator" class="step-indicator">Step 1 of 3</div>

                    <!-- Alert Banner -->
                    <div id="alertBanner" class="alert-banner">
                        <i class="fas fa-check-circle me-2"></i> <span id="alertMessage">OTP sent to email</span>
                    </div>

                    <form id="signupForm" autocomplete="off">
                        @csrf
                        <!-- STEP 1 -->
                        <div id="step1">
                            <div class="mb-4">
                                <label for="businessName" class="form-label">Business Name *</label>
                                <input type="text" id="businessName" name="business_name" class="form-control" placeholder="e.g., Pizza Palace" required>
                                <div class="invalid-feedback" id="businessNameError"></div>
                                <div class="subdomain-preview" id="subdomainPreviewBox" style="display:none;">
                                    Your workspace will be: <strong id="subdomainPreview">...</strong>.<span id="currentHost">localhost</span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="businessType" class="form-label">Business Type *</label>
                                <select id="businessType" name="business_type" class="form-select" required>
                                    <option value="Restaurant">Restaurant</option>
                                    <option value="Retail">Retail</option>
                                    <option value="Grocery">Grocery</option>
                                    <option value="Salon">Salon / Spa</option>
                                    <option value="Cafe">Cafe / Bakery</option>
                                </select>
                            </div>



                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn-back" disabled>&larr; Back</button>
                                <button type="button" class="btn-next" onclick="goToStep(2)">Next &rarr;</button>
                            </div>
                        </div>

                        <!-- STEP 2 -->
                        <div id="step2" style="display:none;">
                            <div class="mb-4">
                                <label for="ownerName" class="form-label">Owner Name *</label>
                                <input type="text" id="ownerName" name="owner_name" class="form-control" placeholder="e.g., Rahul Sharma" required>
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" id="email" name="email" class="form-control" placeholder="owner@yourrestaurant.com" required>
                            </div>

                            <div class="mb-4" id="otpSection">
                                <label class="form-label d-block">Verify email (OTP) *</label>
                                <span class="text-muted small d-block mb-3">We will send a one-time code to this email. Next is enabled only after verification.</span>
                                
                                <div class="d-flex gap-2 mb-3">
                                    <button type="button" id="sendOtpBtn" class="btn btn-outline-primary btn-action-small" onclick="sendEmailOtp()">Send email OTP</button>
                                    <span id="otpSendingSpinner" class="spinner-border spinner-border-sm text-primary my-auto d-none"></span>
                                </div>

                                <div id="otpInputGroup" style="display:none;">
                                    <label for="emailOtp" class="form-label">Email OTP *</label>
                                    <input type="text" id="emailOtp" maxlength="6" class="form-control text-center fw-bold fs-5 mb-3" style="letter-spacing: 5px;" placeholder="******">
                                    <button type="button" id="verifyOtpBtn" class="btn btn-primary btn-action-small" onclick="verifyEmailOtp()">Verify email OTP</button>
                                </div>
                            </div>

                            <!-- Verified Badge -->
                            <div id="verifiedBadge" class="verified-badge" style="display:none;">
                                <i class="fas fa-check-circle"></i> Email verified
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn-back" onclick="goToStep(1)">&larr; Back</button>
                                <button type="button" id="step2NextBtn" class="btn-next" disabled onclick="goToStep(3)">Next &rarr;</button>
                            </div>
                        </div>

                        <!-- STEP 3 -->
                        <div id="step3" style="display:none;">
                            <div class="mb-4">
                                <label for="phone" class="form-label">Phone *</label>
                                <input type="tel" id="phone" name="phone" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="form-control" placeholder="10-digit mobile number" required>
                            </div>

                            <div class="mb-4">
                                <label for="whatsappNumber" class="form-label">WhatsApp Number *</label>
                                <input type="tel" id="whatsappNumber" name="whatsapp_number" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="form-control" placeholder="10-digit WhatsApp number" required>
                                
                                <div class="mt-2 form-check">
                                    <input type="checkbox" class="form-check-input" id="sameAsPhone" checked onchange="toggleSameAsPhone()">
                                    <label class="form-check-label text-muted small fw-bold" for="sameAsPhone">Same as Phone number</label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn-back" onclick="goToStep(2)">&larr; Back</button>
                                <button type="button" class="btn-submit" onclick="submitRegistration()">Launch Workspace &rarr;</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="footer-links">
                    <a href="#">Privacy Policy</a> &bull; <a href="#">Terms & Conditions</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
        <h4 class="loading-title" id="loadingTitle">Setting up your workspace...</h4>
        <p class="loading-subtitle" id="loadingSubtitle">Provisioning database and configuration</p>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let currentStep = 1;
        let isEmailVerified = false;

        // CSRF Token Setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Set dynamic host name in Step 1
        let hostName = window.location.host;
        if (hostName.startsWith('retail.')) {
            hostName = hostName.substring(7); // Remove 'retail.'
        }
        $('#currentHost').text(hostName);

        // Realtime Subdomain Generation
        $('#businessName').on('input', function() {
            let val = $(this).val();
            let slug = val.toLowerCase().replace(/[^a-z0-9]/g, '');
            
            if (slug.length > 0) {
                $('#subdomainPreview').text(slug);
                $('#subdomainPreviewBox').slideDown(200);
            } else {
                $('#subdomainPreviewBox').slideUp(200);
            }
        });

        // Auto Copy Phone to WhatsApp
        $('#phone').on('input', function() {
            if ($('#sameAsPhone').is(':checked')) {
                $('#whatsappNumber').val($(this).val());
            }
        });

        function toggleSameAsPhone() {
            if ($('#sameAsPhone').is(':checked')) {
                $('#whatsappNumber').val($('#phone').val()).attr('readonly', true);
            } else {
                $('#whatsappNumber').attr('readonly', false);
            }
        }

        // Initialize sameAsPhone state
        toggleSameAsPhone();

        function showBannerAlert(msg, type = 'success') {
            $('#alertMessage').text(msg);
            if (type === 'error') {
                $('#alertBanner').css({
                    'background-color': '#fff0f0',
                    'border-left-color': '#ff4757',
                    'color': '#8a1d28'
                });
                $('#alertBanner i').removeClass('fa-check-circle').addClass('fa-exclamation-circle');
            } else {
                $('#alertBanner').css({
                    'background-color': '#ebfffc',
                    'border-left-color': 'var(--success)',
                    'color': '#0d5a4c'
                });
                $('#alertBanner i').removeClass('fa-exclamation-circle').addClass('fa-check-circle');
            }
            $('#alertBanner').addClass('show');
            setTimeout(() => {
                $('#alertBanner').removeClass('show');
            }, 6000);
        }

        function goToStep(step) {
            // Validation before proceeding
            if (step === 2 && currentStep === 1) {
                let name = $('#businessName').val().trim();
                if (!name) {
                    $('#businessName').addClass('is-invalid');
                    return;
                }
                $('#businessName').removeClass('is-invalid');
            }

            if (step === 3 && currentStep === 2) {
                let name = $('#ownerName').val().trim();
                let email = $('#email').val().trim();
                if (!name || !email || !isEmailVerified) {
                    return;
                }
            }

            // Hide all steps and show active
            $('#step1, #step2, #step3').hide();
            $(`#step${step}`).show();

            // Update Progress Bar
            currentStep = step;
            let progressWidth = (step / 3) * 100;
            $('#progressFill').css('width', `${progressWidth}%`);
            $('#stepIndicator').text(`Step ${step} of 3`);
        }

        function sendEmailOtp() {
            let email = $('#email').val().trim();
            if (!email) {
                alert('Please enter your email address first.');
                return;
            }

            $('#sendOtpBtn').prop('disabled', true);
            $('#otpSendingSpinner').removeClass('d-none');

            $.ajax({
                url: "{{ route('central.signup.send-otp') }}",
                method: "POST",
                data: { email: email },
                success: function(res) {
                    $('#otpSendingSpinner').addClass('d-none');
                    $('#sendOtpBtn').text('Resend email OTP').prop('disabled', false);
                    $('#otpInputGroup').slideDown(200);
                    showBannerAlert('OTP sent to email');
                },
                error: function(xhr) {
                    $('#otpSendingSpinner').addClass('d-none');
                    $('#sendOtpBtn').prop('disabled', false);
                    let err = xhr.responseJSON ? xhr.responseJSON.message : 'Error sending OTP';
                    showBannerAlert(err, 'error');
                }
            });
        }

        function verifyEmailOtp() {
            let email = $('#email').val().trim();
            let otp = $('#emailOtp').val().trim();

            if (!otp || otp.length !== 6) {
                alert('Please enter a valid 6-digit OTP code.');
                return;
            }

            $('#verifyOtpBtn').prop('disabled', true);

            $.ajax({
                url: "{{ route('central.signup.verify-otp') }}",
                method: "POST",
                data: { email: email, otp: otp },
                success: function(res) {
                    isEmailVerified = true;
                    $('#otpSection').slideUp(200);
                    $('#verifiedBadge').slideDown(200);
                    $('#email').attr('readonly', true);
                    $('#step2NextBtn').prop('disabled', false);
                    showBannerAlert('Email verified successfully!');
                },
                error: function(xhr) {
                    $('#verifyOtpBtn').prop('disabled', false);
                    let err = xhr.responseJSON ? xhr.responseJSON.message : 'Invalid OTP code.';
                    showBannerAlert(err, 'error');
                }
            });
        }

        function submitRegistration() {
            let phone = $('#phone').val().trim();
            let wa = $('#whatsappNumber').val().trim();

            if (!phone || !wa) {
                alert('Please fill in both phone fields.');
                return;
            }

            let phoneRegex = /^[6-9]\d{9}$/;
            if (!phoneRegex.test(phone)) {
                alert('Please enter a valid 10-digit Indian mobile number starting with 6, 7, 8, or 9.');
                return;
            }
            if (!phoneRegex.test(wa)) {
                alert('Please enter a valid 10-digit Indian WhatsApp number starting with 6, 7, 8, or 9.');
                return;
            }

            // Show loading overlay
            $('#loadingOverlay').addClass('show');
            
            // Dynamic text updates for a premium feel
            let statusTexts = [
                { title: 'Creating secure workspace...', sub: 'Initializing your store subdomain' },
                { title: 'Provisioning database...', sub: 'Creating tenant DB structures' },
                { title: 'Running migrations...', sub: 'Seeding POS default configuration' },
                { title: 'Generating admin profile...', sub: 'Completing setup' }
            ];

            let textIndex = 0;
            let textInterval = setInterval(() => {
                if (textIndex < statusTexts.length) {
                    $('#loadingTitle').text(statusTexts[textIndex].title);
                    $('#loadingSubtitle').text(statusTexts[textIndex].sub);
                    textIndex++;
                } else {
                    clearInterval(textInterval);
                }
            }, 3000);

            // POST form data
            let formData = {
                business_name: $('#businessName').val().trim(),
                business_type: $('#businessType').val(),
                owner_name: $('#ownerName').val().trim(),
                email: $('#email').val().trim(),
                phone: phone,
                whatsapp_number: wa
            };

            $.ajax({
                url: "{{ route('central.signup.register') }}",
                method: "POST",
                data: formData,
                success: function(res) {
                    clearInterval(textInterval);
                    $('#loadingTitle').text('Activation successful!');
                    $('#loadingSubtitle').text('Redirecting to your dashboard...');
                    
                    setTimeout(() => {
                        window.location.href = res.redirect_url;
                    }, 1000);
                },
                error: function(xhr) {
                    clearInterval(textInterval);
                    $('#loadingOverlay').removeClass('show');
                    let err = xhr.responseJSON ? xhr.responseJSON.message : 'Sign up failed. Please check inputs.';
                    alert(err);
                }
            });
        }
    </script>
</body>
</html>
