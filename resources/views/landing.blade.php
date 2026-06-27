@php
    $language = $language ?? 'en';
    $publicUrl = rtrim($publicUrl ?? 'https://hotel.rekayasadigital.com', '/');
    $canonicalUrl = $canonicalUrl ?? $publicUrl.'/';
    $contactEmail = $contactEmail ?? 'admin@rekayasadigital.com';
    $languageAlternates = $languageAlternates ?? [
        'en' => $publicUrl.'/?lang=en',
        'id' => $publicUrl.'/?lang=id',
        'x-default' => $publicUrl.'/',
    ];
    $homeUrl = url('/');
    $loginUrl = route('login');
    $languageSwitchUrls = [
        'en' => url('/?lang=en'),
        'id' => url('/?lang=id'),
    ];

    $copy = [
        'en' => [
            'locale' => 'en_US',
            'dir' => 'ltr',
            'languageName' => 'English',
            'title' => $appName.' | Hotel Meeting Head Counter & QR Attendance Platform',
            'description' => $appName.' is a hotel operations platform for meeting head counts, QR attendance, meal entitlement redemption, room booking visibility, RBAC, audit logs, and real-time reporting.',
            'keywords' => 'hotel head counter, hotel meeting app, QR attendance hotel, meal redemption scanner, banquet head count, meeting room booking, hotel SaaS Indonesia',
            'nav' => [
                'features' => 'Features',
                'workflow' => 'Workflow',
                'security' => 'Security',
                'pricing' => 'Pricing',
                'faq' => 'FAQ',
                'contact' => 'Contact',
                'login' => 'Login',
            ],
            'hero' => [
                'eyebrow' => 'Built for hotel meeting operations',
                'title' => 'Track meeting attendance & meal entitlements in one hotel-ready workspace.',
                'subtitle' => 'Run meeting attendance, participant QR, package redemption, and operational reports from one platform built for multi-hotel operations by Rekayasa Digital.',
                'primary' => 'Get Started',
                'secondary' => 'Open Dashboard',
                'trust' => 'Built by Rekayasa Digital for multi-hotel operations',
            ],
            'stats' => [
                ['value' => 'QR', 'label' => 'secure participant credentials', 'icon' => 'mdi-qrcode-scan'],
                ['value' => 'Live', 'label' => 'dashboard and redemption counters', 'icon' => 'mdi-pulse'],
                ['value' => 'RBAC', 'label' => 'hotel-scoped roles and permissions', 'icon' => 'mdi-security'],
                ['value' => 'Audit', 'label' => 'operational accountability logs', 'icon' => 'mdi-clipboard-text'],
            ],
            'featuresEyebrow' => 'Features',
            'featuresTitle' => 'Everything your hotel needs to count, track, and report',
            'featuresSub' => 'The landing page reflects the real product surface: booking operations, participant QR, redemption, reporting, tenant isolation, and user management.',
            'features' => [
                ['icon' => 'mdi-hotel', 'title' => 'Multi-hotel operations', 'body' => 'Manage hotels, subscriptions, settings, and tenant context with protected hotel-scoped access.'],
                ['icon' => 'mdi-calendar-check', 'title' => 'Booking and room workflow', 'body' => 'Create meeting bookings, assign rooms, track schedules, and keep operations aligned from the booking screen.'],
                ['icon' => 'mdi-account-check', 'title' => 'Participant management', 'body' => 'Register participants, manage attendee details, and connect each guest to the right booking and package.'],
                ['icon' => 'mdi-qrcode-scan', 'title' => 'QR attendance and credentials', 'body' => 'Generate participant QR credentials and support fast check-in flows with scanner-ready tokens.'],
                ['icon' => 'mdi-silverware-fork', 'title' => 'Meal and package redemption', 'body' => 'Verify entitlements, redeem packages, prevent duplicate use, and keep redemption counters live.'],
                ['icon' => 'mdi-view-dashboard', 'title' => 'Operational dashboard', 'body' => 'See current booking activity, attendance signals, redemption progress, and hotel operations at a glance.'],
                ['icon' => 'mdi-file-chart', 'title' => 'Reporting and exports', 'body' => 'Use reporting screens and export workflows for attendance, redemptions, audit review, and operational decisions.'],
                ['icon' => 'mdi-account-key', 'title' => 'RBAC and audit trail', 'body' => 'Control access by role and permission while recording sensitive activity for accountability.'],
            ],
            'stepsEyebrow' => 'How It Works',
            'stepsTitle' => 'From booking to report without spreadsheets',
            'stepsSub' => 'Head Counter keeps the hotel operations flow simple enough for daily use and strict enough for enterprise control.',
            'steps' => [
                ['title' => 'Prepare the booking', 'body' => 'Create the meeting, select hotel room details, define participants, and prepare packages before guests arrive.'],
                ['title' => 'Scan and redeem', 'body' => 'Use QR credentials for attendance and entitlement validation while staff see real-time progress.'],
                ['title' => 'Review and export', 'body' => 'Close the event with dashboards, reports, exports, and audit records ready for management review.'],
            ],
            'highlightEyebrow' => 'Live Dashboard',
            'highlightTitle' => 'See every meeting, every session, every scan — in real time',
            'highlightSub' => 'The operations dashboard gives you a live pulse on every active meeting across your hotel. Track check-in progress, meal redemption rates, and room occupancy without leaving your screen.',
            'highlightList' => [
                'Live check-in counters per meeting room',
                'Meal redemption tracking with progress bars',
                'Active QR token status and rotation monitoring',
                'Attendance breakdowns by session and package',
            ],
            'securityEyebrow' => 'Security & Compliance',
            'securityTitle' => 'Enterprise-grade security, hotel-scoped by design',
            'securitySub' => 'The app is designed around hotel-scoped access, controlled permissions, secure QR handling, and traceable operations.',
            'security' => [
                ['icon' => 'mdi-domain', 'title' => 'Tenant isolation', 'body' => 'Hotel data stays scoped to the active hotel context to reduce cross-property exposure.'],
                ['icon' => 'mdi-account-key', 'title' => 'Role-based permissions', 'body' => 'Admin, hotel user, scanner, reporting, and operational access can be separated cleanly.'],
                ['icon' => 'mdi-lock', 'title' => 'Sensitive token protection', 'body' => 'QR and credential flows avoid exposing raw tokens in logs or public screens.'],
                ['icon' => 'mdi-history', 'title' => 'Audit-ready operations', 'body' => 'Important admin and transactional activity can be reviewed through audit logs.'],
            ],
            'pricingEyebrow' => 'Pricing',
            'pricingTitle' => 'Transparent IDR pricing for every hotel',
            'pricingSub' => 'Start with a simple monthly plan, self-register in minutes, or contact our sales team for enterprise onboarding.',
            'plans' => [
                ['key' => 'starter', 'name' => 'Starter', 'badge' => 'Small hotel', 'price' => 'IDR 100.000', 'userLimit' => 'Up to 5 users', 'desc' => 'For one hotel starting to digitize meeting head counts.', 'items' => ['Core booking workflow', 'Participant QR credentials', 'Redemption scanner', 'Dashboard and reports', 'RBAC and user management (up to 5 users)', 'Email support'], 'cta' => 'register'],
                ['key' => 'professional', 'name' => 'Professional', 'badge' => 'Most popular', 'price' => 'IDR 175.000', 'userLimit' => 'Up to 25 users', 'desc' => 'For active meeting, banquet, and event operations.', 'items' => ['Everything in Starter', 'Advanced reporting access', 'RBAC and user management (up to 25 users)', 'Audit log visibility', 'Priority support'], 'cta' => 'register'],
                ['key' => 'enterprise', 'name' => 'Enterprise', 'badge' => 'Hotel groups', 'price' => 'Custom', 'userLimit' => 'Unlimited users', 'desc' => 'For larger operations that need stronger support and rollout help.', 'items' => ['Everything in Professional', 'Multi-hotel administration', 'Subscription controls', 'RBAC and user management (unlimited users)', 'Custom rollout guidance', 'Dedicated support'], 'cta' => 'contact'],
            ],
            'testimonial' => [
                'quote' => $appName.' helps our meeting operations move faster because attendance, QR validation, and meal redemption can be monitored from one dashboard instead of scattered spreadsheets.',
                'name' => 'General Manager',
                'role' => 'Oria Hotel Jakarta',
            ],
            'faqEyebrow' => 'FAQ',
            'faqTitle' => 'Frequently asked questions',
            'faqSub' => 'Answers for hotel teams evaluating Head Counter on hotel.rekayasadigital.com.',
            'faqs' => [
                ['q' => 'What is Head Counter?', 'a' => $appName.' is a hotel operations platform for meeting head counts, QR attendance, meal entitlement redemption, room booking visibility, RBAC, audit logs, and real-time reporting.'],
                ['q' => 'How does QR attendance work?', 'a' => 'Each participant receives a QR credential. Staff scan the QR at check-in to validate attendance and entitlements in real time, with duplicate-use prevention for redemption.'],
                ['q' => 'Can we manage multiple hotels?', 'a' => 'Yes. The platform supports multi-hotel administration with hotel-scoped data isolation, per-hotel roles, and subscription controls for hotel groups.'],
                ['q' => 'What reports are available?', 'a' => 'Head Counter includes reporting screens and export workflows for attendance, redemptions, audit review, and operational decisions across bookings and sessions.'],
                ['q' => 'How is data secured?', 'a' => 'Data is hotel-scoped by design, access is controlled through RBAC permissions, QR tokens are never exposed in logs, and sensitive activity is recorded in audit logs for accountability.'],
                ['q' => 'How do we get started?', 'a' => 'Choose the Starter or Professional plan to self-register in minutes, or contact our sales team for enterprise onboarding and custom rollout.'],
            ],
            'contactEyebrow' => 'Contact',
            'contactTitle' => 'Ready to modernize hotel meeting operations?',
            'contactSub' => 'Send us a message and our team will reply within one business day.',
            'footerText' => 'Hotel meeting head counter, QR attendance, package redemption, and reporting platform.',
            'builtBy' => 'Built by',
            'footer' => [
                'product' => 'Product',
                'company' => 'Company',
                'resources' => 'Resources',
                'features' => 'Features',
                'workflow' => 'Workflow',
                'security' => 'Security',
                'pricing' => 'Pricing',
                'faq' => 'FAQ',
                'login' => 'Login',
                'about' => 'About',
                'contact' => 'Contact',
                'sitemap' => 'Sitemap',
                'privacy' => 'Privacy',
            ],
            'forms' => [
                'name' => 'Full name',
                'email' => 'Email address',
                'hotel' => 'Hotel name (optional)',
                'subject' => 'Subject',
                'message' => 'Message',
                'plan' => 'Selected plan',
                'consent' => 'I agree that Rekayasa Digital may contact me about my request.',
                'send' => 'Send message',
                'register' => 'Request registration',
                'sending' => 'Sending...',
                'successContact' => 'Thank you! Our team will reply to your message within one business day.',
                'successRegister' => 'Thank you! Our team will review your registration and reach out within one business day.',
                'error' => 'We could not send your message right now. Please email admin@rekayasadigital.com directly.',
                'validationError' => 'Please complete all required fields correctly.',
                'close' => 'Close',
            ],
            'contactModalTitle' => 'Contact sales',
            'registerModalTitle' => 'Register for Head Counter',
            'cookie' => [
                'title' => 'Cookie settings',
                'body' => 'We use necessary cookies to remember your language and consent choices. Optional analytics and marketing cookies help us improve the public landing page.',
                'accept' => 'Accept all',
                'reject' => 'Reject optional',
                'settings' => 'Settings',
                'save' => 'Save preferences',
                'necessary' => 'Necessary cookies',
                'necessaryBody' => 'Required for language preference and consent storage.',
                'analytics' => 'Analytics cookies',
                'analyticsBody' => 'Help us understand landing page performance.',
                'marketing' => 'Marketing cookies',
                'marketingBody' => 'Support future campaign measurement.',
            ],
        ],
        'id' => [
            'locale' => 'id_ID',
            'dir' => 'ltr',
            'languageName' => 'Indonesia',
            'title' => $appName.' | Platform Head Counter Meeting Hotel & Absensi QR',
            'description' => $appName.' adalah platform operasional hotel untuk head count meeting, absensi QR, redeem paket makan, booking room, RBAC, audit log, dan laporan real-time.',
            'keywords' => 'hotel head counter, aplikasi meeting hotel, absensi QR hotel, scanner redeem makan, banquet head count, booking ruang meeting, SaaS hotel Indonesia',
            'nav' => [
                'features' => 'Fitur',
                'workflow' => 'Alur Kerja',
                'security' => 'Keamanan',
                'pricing' => 'Harga',
                'faq' => 'FAQ',
                'contact' => 'Kontak',
                'login' => 'Login',
            ],
            'hero' => [
                'eyebrow' => 'Dibuat untuk operasional meeting hotel',
                'title' => 'Pantau absensi meeting & redeem makan dalam satu workspace yang siap digunakan hotel.',
                'subtitle' => 'Kelola absensi meeting, QR peserta, redeem paket, dan laporan operasional dari satu platform yang dibangun untuk operasional multi-hotel oleh Rekayasa Digital.',
                'primary' => 'Mulai Sekarang',
                'secondary' => 'Buka Dashboard',
                'trust' => 'Dibangun oleh Rekayasa Digital untuk operasional multi-hotel',
            ],
            'stats' => [
                ['value' => 'QR', 'label' => 'kredensial peserta yang aman', 'icon' => 'mdi-qrcode-scan'],
                ['value' => 'Live', 'label' => 'dashboard dan counter redeem', 'icon' => 'mdi-pulse'],
                ['value' => 'RBAC', 'label' => 'role dan permission per hotel', 'icon' => 'mdi-security'],
                ['value' => 'Audit', 'label' => 'log akuntabilitas operasional', 'icon' => 'mdi-clipboard-text'],
            ],
            'featuresEyebrow' => 'Fitur',
            'featuresTitle' => 'Semua yang dibutuhkan hotel untuk menghitung, memantau, dan melaporkan',
            'featuresSub' => 'Landing page ini menggambarkan produk sebenarnya: booking, QR peserta, redemption, reporting, isolasi tenant, dan manajemen user.',
            'features' => [
                ['icon' => 'mdi-hotel', 'title' => 'Operasional multi-hotel', 'body' => 'Kelola hotel, subscription, setting, dan konteks tenant dengan akses yang dibatasi per hotel.'],
                ['icon' => 'mdi-calendar-check', 'title' => 'Booking dan ruang meeting', 'body' => 'Buat booking meeting, pilih ruangan, pantau jadwal, dan jaga operasional tetap rapi.'],
                ['icon' => 'mdi-account-check', 'title' => 'Manajemen peserta', 'body' => 'Daftarkan peserta, kelola detail attendee, dan hubungkan tamu dengan booking serta paket yang benar.'],
                ['icon' => 'mdi-qrcode-scan', 'title' => 'Absensi QR dan kredensial', 'body' => 'Generate kredensial QR peserta dan dukung proses check-in cepat dengan token siap scan.'],
                ['icon' => 'mdi-silverware-fork', 'title' => 'Redeem makan dan paket', 'body' => 'Validasi entitlement, redeem paket, cegah penggunaan ganda, dan lihat counter secara live.'],
                ['icon' => 'mdi-view-dashboard', 'title' => 'Dashboard operasional', 'body' => 'Pantau aktivitas booking, sinyal absensi, progress redeem, dan kondisi operasional hotel.'],
                ['icon' => 'mdi-file-chart', 'title' => 'Reporting dan export', 'body' => 'Gunakan halaman report dan export untuk absensi, redemption, audit, dan keputusan operasional.'],
                ['icon' => 'mdi-account-key', 'title' => 'RBAC dan audit trail', 'body' => 'Atur akses berdasarkan role dan permission sambil merekam aktivitas penting untuk akuntabilitas.'],
            ],
            'stepsEyebrow' => 'Cara Kerja',
            'stepsTitle' => 'Dari booking sampai laporan tanpa spreadsheet',
            'stepsSub' => 'Head Counter membuat alur operasional hotel tetap simpel untuk harian, tetapi tetap kuat untuk kontrol enterprise.',
            'steps' => [
                ['title' => 'Siapkan booking', 'body' => 'Buat meeting, pilih detail ruangan hotel, masukkan peserta, dan siapkan paket sebelum tamu datang.'],
                ['title' => 'Scan dan redeem', 'body' => 'Gunakan QR peserta untuk absensi dan validasi entitlement sambil melihat progress real-time.'],
                ['title' => 'Review dan export', 'body' => 'Tutup event dengan dashboard, report, export, dan audit record yang siap direview manajemen.'],
            ],
            'highlightEyebrow' => 'Dashboard Live',
            'highlightTitle' => 'Pantau setiap meeting, setiap sesi, setiap scan — secara real-time',
            'highlightSub' => 'Dashboard operasional memberi pulse live pada setiap meeting aktif di hotel Anda. Pantau progres check-in, rate redeem makan, dan okupansi ruangan tanpa pindah layar.',
            'highlightList' => [
                'Counter check-in live per ruang meeting',
                'Tracking redeem makan dengan progress bar',
                'Status token QR aktif dan rotasi',
                'Breakdown absensi per sesi dan paket',
            ],
            'securityEyebrow' => 'Keamanan & Kepatuhan',
            'securityTitle' => 'Keamanan kelas enterprise, terisolasi per hotel',
            'securitySub' => 'Aplikasi dirancang dengan akses per hotel, permission terkontrol, QR yang aman, dan aktivitas yang dapat ditelusuri.',
            'security' => [
                ['icon' => 'mdi-domain', 'title' => 'Isolasi tenant', 'body' => 'Data hotel tetap berada dalam konteks hotel aktif untuk mengurangi risiko akses lintas properti.'],
                ['icon' => 'mdi-account-key', 'title' => 'Permission berbasis role', 'body' => 'Akses admin, user hotel, scanner, reporting, dan operasional dapat dipisahkan dengan rapi.'],
                ['icon' => 'mdi-lock', 'title' => 'Proteksi token sensitif', 'body' => 'Alur QR dan kredensial menghindari ekspos raw token pada log atau layar publik.'],
                ['icon' => 'mdi-history', 'title' => 'Operasional siap audit', 'body' => 'Aktivitas admin dan transaksi penting dapat direview melalui audit log.'],
            ],
            'pricingEyebrow' => 'Harga',
            'pricingTitle' => 'Harga transparan dalam IDR untuk setiap hotel',
            'pricingSub' => 'Mulai dengan paket bulanan sederhana, registrasi mandiri dalam hitungan menit, atau hubungi tim sales untuk onboarding enterprise.',
            'plans' => [
                ['key' => 'starter', 'name' => 'Starter', 'badge' => 'Hotel kecil', 'price' => 'IDR 100.000', 'userLimit' => 'Hingga 5 user', 'desc' => 'Untuk satu hotel yang mulai mendigitalisasi head count meeting.', 'items' => ['Workflow booking inti', 'Kredensial QR peserta', 'Scanner redemption', 'Dashboard dan report', 'RBAC dan user management (hingga 5 user)', 'Email support'], 'cta' => 'register'],
                ['key' => 'professional', 'name' => 'Professional', 'badge' => 'Paling populer', 'price' => 'IDR 175.000', 'userLimit' => 'Hingga 25 user', 'desc' => 'Untuk operasional meeting, banquet, dan event yang aktif.', 'items' => ['Semua fitur Starter', 'Akses reporting lanjutan', 'RBAC dan user management (hingga 25 user)', 'Visibilitas audit log', 'Priority support'], 'cta' => 'register'],
                ['key' => 'enterprise', 'name' => 'Enterprise', 'badge' => 'Group hotel', 'price' => 'Custom', 'userLimit' => 'User tak terbatas', 'desc' => 'Untuk operasional lebih besar yang membutuhkan support dan rollout lebih kuat.', 'items' => ['Semua fitur Professional', 'Administrasi multi-hotel', 'Kontrol subscription', 'RBAC dan user management (user tak terbatas)', 'Panduan rollout custom', 'Dedicated support'], 'cta' => 'contact'],
            ],
            'testimonial' => [
                'quote' => $appName.' membantu operasional meeting berjalan lebih cepat karena absensi, validasi QR, dan redeem makan dapat dipantau dari satu dashboard, bukan lagi dari spreadsheet terpisah.',
                'name' => 'General Manager',
                'role' => 'Oria Hotel Jakarta',
            ],
            'faqEyebrow' => 'FAQ',
            'faqTitle' => 'Pertanyaan yang sering diajukan',
            'faqSub' => 'Jawaban untuk tim hotel yang mengevaluasi Head Counter di hotel.rekayasadigital.com.',
            'faqs' => [
                ['q' => 'Apa itu Head Counter?', 'a' => $appName.' adalah platform operasional hotel untuk head count meeting, absensi QR, redeem paket makan, booking room, RBAC, audit log, dan laporan real-time.'],
                ['q' => 'Bagaimana cara kerja absensi QR?', 'a' => 'Setiap peserta mendapat kredensial QR. Staff men-scan QR saat check-in untuk memvalidasi absensi dan entitlement secara real-time, dengan pencegahan penggunaan ganda untuk redemption.'],
                ['q' => 'Apakah kita bisa mengelola beberapa hotel?', 'a' => 'Ya. Platform mendukung administrasi multi-hotel dengan isolasi data per hotel, role per hotel, dan kontrol subscription untuk group hotel.'],
                ['q' => 'Laporan apa saja yang tersedia?', 'a' => 'Head Counter memiliki halaman report dan export untuk absensi, redemption, audit, dan keputusan operasional di berbagai booking dan sesi.'],
                ['q' => 'Bagaimana keamanan data dijaga?', 'a' => 'Data diisolasi per hotel, akses dikontrol melalui permission RBAC, token QR tidak pernah diekspos di log, dan aktivitas penting direkam di audit log untuk akuntabilitas.'],
                ['q' => 'Bagaimana cara memulai?', 'a' => 'Pilih paket Starter atau Professional untuk registrasi mandiri dalam hitungan menit, atau hubungi tim sales untuk onboarding enterprise dan rollout custom.'],
            ],
            'contactEyebrow' => 'Kontak',
            'contactTitle' => 'Siap memodernisasi operasional meeting hotel?',
            'contactSub' => 'Kirim pesan dan tim kami akan membalas dalam satu hari kerja.',
            'footerText' => 'Platform head counter meeting hotel, absensi QR, redeem paket, dan reporting.',
            'builtBy' => 'Dibangun oleh',
            'footer' => [
                'product' => 'Produk',
                'company' => 'Perusahaan',
                'resources' => 'Sumber Daya',
                'features' => 'Fitur',
                'workflow' => 'Alur Kerja',
                'security' => 'Keamanan',
                'pricing' => 'Harga',
                'faq' => 'FAQ',
                'login' => 'Login',
                'about' => 'Tentang',
                'contact' => 'Kontak',
                'sitemap' => 'Sitemap',
                'privacy' => 'Privasi',
            ],
            'forms' => [
                'name' => 'Nama lengkap',
                'email' => 'Alamat email',
                'hotel' => 'Nama hotel (opsional)',
                'subject' => 'Subjek',
                'message' => 'Pesan',
                'plan' => 'Paket dipilih',
                'consent' => 'Saya setuju Rekayasa Digital dapat menghubungi saya terkait permintaan ini.',
                'send' => 'Kirim pesan',
                'register' => 'Ajukan registrasi',
                'sending' => 'Mengirim...',
                'successContact' => 'Terima kasih! Tim kami akan membalas pesan Anda dalam satu hari kerja.',
                'successRegister' => 'Terima kasih! Tim kami akan meninjau registrasi Anda dan menghubungi dalam satu hari kerja.',
                'error' => 'Pesan tidak dapat dikirim saat ini. Silakan email admin@rekayasadigital.com langsung.',
                'validationError' => 'Mohon lengkapi semua field yang wajib diisi dengan benar.',
                'close' => 'Tutup',
            ],
            'contactModalTitle' => 'Hubungi sales',
            'registerModalTitle' => 'Daftar Head Counter',
            'cookie' => [
                'title' => 'Pengaturan cookie',
                'body' => 'Kami menggunakan cookie wajib untuk menyimpan bahasa dan pilihan consent. Cookie analytics dan marketing opsional membantu meningkatkan landing page publik.',
                'accept' => 'Terima semua',
                'reject' => 'Tolak opsional',
                'settings' => 'Pengaturan',
                'save' => 'Simpan pilihan',
                'necessary' => 'Cookie wajib',
                'necessaryBody' => 'Dibutuhkan untuk preferensi bahasa dan penyimpanan consent.',
                'analytics' => 'Cookie analytics',
                'analyticsBody' => 'Membantu memahami performa landing page.',
                'marketing' => 'Cookie marketing',
                'marketingBody' => 'Mendukung pengukuran campaign di masa depan.',
            ],
        ],
    ];

    $t = $copy[$language] ?? $copy['en'];
    $ogImage = $publicUrl.'/images/logo-full.png';
    $contactHref = 'mailto:'.$contactEmail.'?subject='.rawurlencode($appName.' inquiry');
    $jsI18n = [
        'sending' => $t['forms']['sending'],
        'send' => $t['forms']['send'],
        'register' => $t['forms']['register'],
        'successContact' => $t['forms']['successContact'],
        'successRegister' => $t['forms']['successRegister'],
        'error' => $t['forms']['error'],
        'validationError' => $t['forms']['validationError'],
        'close' => $t['forms']['close'],
        'contactModalTitle' => $t['contactModalTitle'],
        'registerModalTitle' => $t['registerModalTitle'],
    ];
    $jsonLd = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Organization',
                '@id' => $publicUrl.'/#organization',
                'name' => 'Rekayasa Digital',
                'url' => 'https://rekayasadigital.com',
                'email' => $contactEmail,
            ],
            [
                '@type' => 'SoftwareApplication',
                '@id' => $publicUrl.'/#software',
                'name' => $appName,
                'applicationCategory' => 'BusinessApplication',
                'operatingSystem' => 'Web',
                'url' => $canonicalUrl,
                'description' => $t['description'],
                'creator' => ['@id' => $publicUrl.'/#organization'],
                'offers' => [
                    ['@type' => 'Offer', 'name' => 'Starter', 'price' => '100000', 'priceCurrency' => 'IDR', 'availability' => 'https://schema.org/InStock'],
                    ['@type' => 'Offer', 'name' => 'Professional', 'price' => '175000', 'priceCurrency' => 'IDR', 'availability' => 'https://schema.org/InStock'],
                    ['@type' => 'Offer', 'name' => 'Enterprise', 'priceCurrency' => 'IDR', 'description' => 'Custom pricing for hotel groups', 'availability' => 'https://schema.org/InStock'],
                ],
                'featureList' => array_column($t['features'], 'title'),
            ],
            [
                '@type' => 'FAQPage',
                '@id' => $publicUrl.'/#faq',
                'mainEntity' => array_map(fn ($faq) => [
                    '@type' => 'Question',
                    'name' => $faq['q'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['a']],
                ], $t['faqs']),
            ],
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $language }}" dir="{{ $t['dir'] }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $t['title'] }}</title>
    <meta name="title" content="{{ $t['title'] }}">
    <meta name="description" content="{{ $t['description'] }}">
    <meta name="keywords" content="{{ $t['keywords'] }}">
    <meta name="author" content="Rekayasa Digital">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="googlebot" content="index, follow">
    <meta name="language" content="{{ $t['languageName'] }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <link rel="alternate" hreflang="en" href="{{ $languageAlternates['en'] }}">
    <link rel="alternate" hreflang="id" href="{{ $languageAlternates['id'] }}">
    <link rel="alternate" hreflang="x-default" href="{{ $languageAlternates['x-default'] }}">

    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:title" content="{{ $t['title'] }}">
    <meta property="og:description" content="{{ $t['description'] }}">
    <meta property="og:site_name" content="{{ $appName }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:locale" content="{{ $t['locale'] }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $t['title'] }}">
    <meta name="twitter:description" content="{{ $t['description'] }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/icon/favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/css/icons/material-design-iconic-font/css/materialdesignicons.min.css') }}" rel="stylesheet">
    <script type="application/ld+json">@json($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>

    <style>
        :root {
            --hc-navy: #101820;
            --hc-navy-2: #16263a;
            --hc-teal: #0f766e;
            --hc-teal-light: #14b8a6;
            --hc-teal-glow: #26c6da;
            --hc-orange: #f97316;
            --hc-amber: #f59e0b;
            --hc-indigo: #6366f1;
            --hc-ink: #1e293b;
            --hc-muted: #64748b;
            --hc-bg: #f8fafc;
            --hc-line: #e2e8f0;
            --hc-white: #fff;
            --hc-radius: 14px;
            --hc-radius-sm: 10px;
            --hc-shadow-sm: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
            --hc-shadow: 0 4px 24px rgba(0,0,0,.06), 0 1px 4px rgba(0,0,0,.04);
            --hc-shadow-lg: 0 24px 60px rgba(2,6,23,.10), 0 8px 24px rgba(2,6,23,.06);
            --hc-max: 1180px;
            --hc-tr: .25s cubic-bezier(.4,0,.2,1);
        }
        *{box-sizing:border-box}
        html{max-width:100%;overflow-x:hidden;scroll-behavior:smooth}
        body{margin:0;max-width:100%;overflow-x:hidden;font-family:Inter,system-ui,-apple-system,'Segoe UI',sans-serif;color:var(--hc-ink);background:var(--hc-white);line-height:1.6;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}
        a{color:var(--hc-teal);text-decoration:none;transition:color var(--hc-tr)}
        a:hover{color:var(--hc-teal-light)}
        img{max-width:100%;display:block}
        button{font:inherit;cursor:pointer}
        .hc-container{width:min(100% - 32px,var(--hc-max));max-width:100%;margin:0 auto}
        .hc-section{padding:84px 0}
        .hc-section,.hc-footer{max-width:100%;overflow-x:hidden}
        .hc-section.alt{background:var(--hc-bg)}
        .hc-center{text-align:center;margin-inline:auto}
        .hc-eyebrow{display:inline-flex;align-items:center;gap:7px;padding:7px 14px;border-radius:999px;background:rgba(20,184,166,.08);color:var(--hc-teal);font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
        .hc-section h2{margin:22px 0 14px;font-size:clamp(30px,4vw,44px);font-weight:800;line-height:1.12;letter-spacing:-.02em}
        .hc-section-head{max-width:680px;margin-bottom:44px}
        .hc-section-head p{margin:0;color:var(--hc-muted);font-size:18px;line-height:1.7}
        .hc-center .hc-section-head{margin-inline:auto}

        .hc-nav{position:fixed;inset:0 0 auto;z-index:50;max-width:100vw;overflow-x:hidden;background:rgba(255,255,255,.88);border-bottom:1px solid transparent;backdrop-filter:blur(14px);transition:box-shadow var(--hc-tr),background var(--hc-tr)}
        .hc-nav.scrolled{background:rgba(255,255,255,.96);box-shadow:var(--hc-shadow-sm);border-bottom-color:var(--hc-line)}
        .hc-nav-inner{min-height:70px;display:flex;align-items:center;justify-content:space-between;gap:20px}
        .hc-brand{display:inline-flex;align-items:center;gap:10px;font-weight:800;font-size:18px;color:var(--hc-navy)}
        .hc-brand img{height:34px}
        .hc-nav-links{display:flex;align-items:center;gap:26px;list-style:none;margin:0;padding:0}
        .hc-nav-links a{color:var(--hc-ink);font-weight:600;font-size:15px;padding:6px 0}
        .hc-nav-links a:hover{color:var(--hc-teal)}
        .hc-nav-actions{display:flex;align-items:center;gap:12px}
        .hc-lang{display:inline-flex;border:1px solid var(--hc-line);border-radius:999px;overflow:hidden}
        .hc-lang a{padding:5px 10px;color:var(--hc-muted);font-size:12px;font-weight:800}
        .hc-lang a.active{color:#fff;background:linear-gradient(135deg,var(--hc-teal),var(--hc-indigo))}
        .hc-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:44px;padding:0 22px;border:1px solid transparent;border-radius:10px;font-weight:700;font-size:15px;transition:all var(--hc-tr);white-space:nowrap}
        .hc-btn-primary{color:#fff;background:var(--hc-teal);box-shadow:0 4px 14px rgba(15,118,110,.28)}
        .hc-btn-primary:hover{background:var(--hc-teal-light);color:#fff;transform:translateY(-1px);box-shadow:0 6px 20px rgba(15,118,110,.36)}
        .hc-btn-ghost{color:var(--hc-ink);background:transparent;border-color:var(--hc-line)}
        .hc-btn-ghost:hover{background:var(--hc-bg);color:var(--hc-ink)}
        .hc-btn-light{background:rgba(255,255,255,.12);color:#fff;border-color:rgba(255,255,255,.24);backdrop-filter:blur(4px)}
        .hc-btn-light:hover{background:rgba(255,255,255,.2);color:#fff;transform:translateY(-1px)}
        .hc-btn-lg{min-height:50px;padding:0 30px;font-size:16px}
        .hc-nav-toggle{display:none;width:42px;height:42px;border:1px solid var(--hc-line);border-radius:10px;background:#fff;color:var(--hc-ink);align-items:center;justify-content:center}
        .hc-mobile{display:none;border-top:1px solid var(--hc-line);padding:12px 0 18px}
        .hc-mobile.open{display:block}
        .hc-mobile a{display:block;padding:11px 0;color:var(--hc-ink);font-weight:700}

        .hc-hero{position:relative;padding:130px 0 100px;color:#fff;overflow:hidden;background:linear-gradient(150deg,var(--hc-navy) 0%,var(--hc-navy-2) 45%,#0c1a28 100%)}
        .hc-hero::before{content:'';position:absolute;top:-20%;right:-10%;width:600px;height:600px;background:radial-gradient(circle,rgba(38,198,218,.10),transparent 65%);pointer-events:none}
        .hc-hero::after{content:'';position:absolute;bottom:-30%;left:-10%;width:500px;height:500px;background:radial-gradient(circle,rgba(99,102,241,.08),transparent 65%);pointer-events:none}
        .hc-hero-grid{position:relative;z-index:1;display:grid;grid-template-columns:1fr 1fr;gap:56px;align-items:center}
        .hc-hero-eyebrow{display:inline-flex;align-items:center;gap:8px;padding:7px 16px;border-radius:999px;border:1px solid rgba(255,255,255,.2);background:rgba(255,255,255,.08);color:#cffafe;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;margin-bottom:22px}
        .hc-hero-eyebrow .mdi{font-size:18px;color:var(--hc-teal-glow)}
        .hc-hero h1{font-size:clamp(36px,5vw,52px);font-weight:800;line-height:1.08;letter-spacing:-.025em;margin:0 0 22px;color:#fff}
        .hc-hero h1 .hc-accent{background:linear-gradient(90deg,var(--hc-teal-glow),var(--hc-teal-light));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
        .hc-hero p{font-size:clamp(17px,2vw,19px);color:rgba(255,255,255,.82);line-height:1.65;margin:0 0 34px;max-width:540px}
        .hc-hero-cta{display:flex;gap:14px;flex-wrap:wrap}
        .hc-hero-trust{display:flex;align-items:center;gap:10px;margin-top:30px;color:rgba(255,255,255,.6);font-size:14px}
        .hc-hero-trust .mdi{color:var(--hc-teal-glow)}

        .hc-hero-mock{position:relative}
        .hc-hero-card{background:rgba(255,255,255,.96);border-radius:18px;box-shadow:0 30px 80px rgba(0,0,0,.4),0 12px 32px rgba(0,0,0,.2);overflow:hidden;transform:perspective(1200px) rotateY(-3deg) rotateX(1deg);transition:transform .5s ease}
        .hc-hero-card:hover{transform:perspective(1200px) rotateY(0) rotateX(0)}
        .hc-hero-card-top{display:flex;align-items:center;gap:8px;padding:14px 18px;border-bottom:1px solid var(--hc-line);background:var(--hc-bg)}
        .hc-hero-card-top .hc-dot{width:10px;height:10px;border-radius:50%}
        .hc-hero-card-top .hc-dot.r{background:#ef4444}.hc-hero-card-top .hc-dot.y{background:#f59e0b}.hc-hero-card-top .hc-dot.g{background:#22c55e}
        .hc-hero-card-top b{margin-left:8px;font-size:13px;color:var(--hc-muted);font-weight:500}
        .hc-hero-card-body{padding:22px}
        .hc-mock-row{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-radius:10px;background:var(--hc-bg);margin-bottom:10px}
        .hc-mock-row:last-child{margin-bottom:0}
        .hc-mock-label{display:flex;align-items:center;gap:10px;font-size:13px;font-weight:600}
        .hc-mock-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:18px}
        .hc-fic-teal{background:rgba(20,184,166,.10);color:var(--hc-teal)}
        .hc-fic-orange{background:rgba(249,115,22,.10);color:#ea580c}
        .hc-fic-indigo{background:rgba(99,102,241,.10);color:var(--hc-indigo)}
        .hc-fic-green{background:rgba(34,197,94,.10);color:#16a34a}
        .hc-mock-value{font-size:12px;font-weight:700;padding:4px 10px;border-radius:999px}
        .hc-badge-live{background:rgba(34,197,94,.12);color:#16a34a}
        .hc-badge-teal{background:rgba(20,184,166,.12);color:var(--hc-teal)}
        .hc-badge-orange{background:rgba(249,115,22,.12);color:#ea580c}

        .hc-stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:24px;text-align:center}
        .hc-stat{display:flex;flex-direction:column;align-items:center;justify-content:flex-start}
        .hc-stat-icon{width:56px;height:56px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:28px;margin-bottom:14px;background:linear-gradient(135deg,rgba(20,184,166,.10),rgba(99,102,241,.10));color:var(--hc-teal);transition:transform var(--hc-tr),box-shadow var(--hc-tr)}
        .hc-stat:hover .hc-stat-icon{transform:translateY(-4px) scale(1.06);box-shadow:0 8px 24px rgba(15,118,110,.18)}
        .hc-stat h3{font-size:38px;font-weight:800;color:var(--hc-navy);margin:0;letter-spacing:-.02em}
        .hc-stat p{font-size:14px;color:var(--hc-muted);margin:6px 0 0;font-weight:500}

        .hc-features-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-top:50px}
        .hc-feature-card{padding:26px;border-radius:var(--hc-radius);background:#fff;border:1px solid var(--hc-line);transition:all var(--hc-tr)}
        .hc-feature-card:hover{border-color:rgba(20,184,166,.3);box-shadow:var(--hc-shadow);transform:translateY(-3px)}
        .hc-feature-icon{width:50px;height:50px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:26px;margin-bottom:18px;background:linear-gradient(135deg,var(--hc-teal),var(--hc-indigo));color:#fff}
        .hc-feature-card h3{font-size:17px;font-weight:700;margin:0 0 8px}
        .hc-feature-card p{font-size:14px;color:var(--hc-muted);line-height:1.6;margin:0}

        .hc-steps{display:grid;grid-template-columns:repeat(3,1fr);gap:28px;margin-top:50px}
        .hc-step{text-align:center}
        .hc-step-num{width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--hc-teal),var(--hc-teal-light));color:#fff;font-size:26px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;box-shadow:0 8px 24px rgba(15,118,110,.24)}
        .hc-step h3{font-size:20px;font-weight:700;margin:0 0 8px}
        .hc-step p{font-size:15px;color:var(--hc-muted);line-height:1.65;margin:0}

        .hc-highlight-grid{display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center}
        .hc-highlight-visual{background:linear-gradient(150deg,var(--hc-navy),var(--hc-navy-2));border-radius:20px;padding:34px;position:relative;overflow:hidden}
        .hc-highlight-visual::before{content:'';position:absolute;top:-40%;right:-40%;width:400px;height:400px;background:radial-gradient(circle,rgba(38,198,218,.12),transparent 65%)}
        .hc-hl-card{position:relative;z-index:1;background:rgba(255,255,255,.96);border-radius:14px;padding:16px 20px;margin-bottom:14px}
        .hc-hl-card:last-child{margin-bottom:0}
        .hc-hl-card-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
        .hc-hl-card-head b{font-size:14px}
        .hc-hl-bar{height:8px;border-radius:4px;background:#e2e8f0;overflow:hidden}
        .hc-hl-bar-fill{height:100%;border-radius:4px}
        .hc-bar-teal{background:linear-gradient(90deg,var(--hc-teal),var(--hc-teal-glow))}
        .hc-bar-orange{background:linear-gradient(90deg,var(--hc-orange),var(--hc-amber))}
        .hc-bar-indigo{background:linear-gradient(90deg,var(--hc-indigo),#818cf8)}
        .hc-hl-card-foot{display:flex;justify-content:space-between;font-size:12px;color:var(--hc-muted);margin-top:6px}
        .hc-highlight-list{list-style:none;padding:0;margin:24px 0 0}
        .hc-highlight-list li{display:flex;align-items:flex-start;gap:12px;padding:8px 0;font-size:15px}
        .hc-highlight-list .mdi{flex-shrink:0;width:24px;height:24px;border-radius:50%;background:rgba(34,197,94,.12);color:#16a34a;display:flex;align-items:center;justify-content:center;font-size:16px;margin-top:2px}

        .hc-security-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:20px;margin-top:50px}
        .hc-sec-card{display:flex;gap:16px;align-items:flex-start;padding:24px;border-radius:var(--hc-radius);border:1px solid var(--hc-line);background:#fff;transition:all var(--hc-tr)}
        .hc-sec-card:hover{border-color:rgba(20,184,166,.24);box-shadow:var(--hc-shadow);transform:translateY(-2px)}
        .hc-sec-icon{flex-shrink:0;width:46px;height:46px;border-radius:12px;background:rgba(15,118,110,.08);color:var(--hc-teal);display:flex;align-items:center;justify-content:center;font-size:24px}
        .hc-sec-card h3{font-size:17px;font-weight:700;margin:0 0 4px}
        .hc-sec-card p{font-size:14px;color:var(--hc-muted);margin:0;line-height:1.6}

        .hc-pricing-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-top:50px;align-items:start}
        .hc-price-card{background:#fff;border:1px solid var(--hc-line);border-radius:var(--hc-radius);padding:32px;transition:all var(--hc-tr)}
        .hc-price-card:hover{box-shadow:var(--hc-shadow);transform:translateY(-3px)}
        .hc-price-featured{border-color:var(--hc-teal);border-width:2px;box-shadow:0 12px 40px rgba(15,118,110,.12)}
        .hc-price-badge{display:inline-block;padding:4px 12px;border-radius:999px;font-size:12px;font-weight:700;background:rgba(20,184,166,.10);color:var(--hc-teal);margin-bottom:8px}
        .hc-price-card h3{font-size:18px;font-weight:700;margin:0}
        .hc-price-tag{font-size:38px;font-weight:800;letter-spacing:-.02em;margin:14px 0 0}
        .hc-price-tag span{font-size:15px;font-weight:500;color:var(--hc-muted)}
        .hc-price-userlimit{display:inline-flex;align-items:center;gap:6px;margin-top:6px;font-size:13px;font-weight:600;color:var(--hc-teal);background:rgba(20,184,166,.08);padding:4px 12px;border-radius:999px}
        .hc-price-userlimit .mdi{font-size:16px}
        .hc-price-desc{font-size:14px;color:var(--hc-muted);margin:8px 0 22px}
        .hc-price-features{list-style:none;padding:0;margin:0}
        .hc-price-features li{display:flex;align-items:center;gap:10px;padding:9px 0;font-size:14px;border-top:1px solid var(--hc-bg)}
        .hc-price-features li:first-child{border-top:none}
        .hc-price-features .mdi{color:var(--hc-teal-light);font-size:18px;flex-shrink:0}
        .hc-price-card .hc-btn{width:100%;margin-top:20px}

        .hc-testimonial{max-width:800px;margin:0 auto;text-align:center}
        .hc-testimonial .mdi-format-quote{font-size:40px;color:rgba(20,184,166,.2)}
        .hc-testimonial blockquote{font-size:clamp(20px,3vw,26px);line-height:1.5;font-weight:500;color:var(--hc-ink);margin:16px 0 24px;letter-spacing:-.01em}
        .hc-testi-author{display:inline-flex;align-items:center;gap:14px}
        .hc-testi-avatar{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,var(--hc-teal),var(--hc-indigo));color:#fff;font-weight:700;font-size:18px;display:flex;align-items:center;justify-content:center}
        .hc-testi-author b{font-size:16px;display:block}
        .hc-testi-author span{font-size:14px;color:var(--hc-muted)}

        .hc-faq-list{max-width:760px;margin:0 auto}
        .hc-faq-item{border:1px solid var(--hc-line);border-radius:var(--hc-radius-sm);margin-bottom:14px;background:#fff;overflow:hidden}
        .hc-faq-q{display:flex;align-items:center;justify-content:space-between;width:100%;text-align:left;padding:20px 24px;background:none;border:none;font-size:17px;font-weight:600;color:var(--hc-ink)}
        .hc-faq-q .mdi{transition:transform var(--hc-tr);color:var(--hc-teal);font-size:22px}
        .hc-faq-item.open .hc-faq-q .mdi{transform:rotate(180deg)}
        .hc-faq-a{max-height:0;overflow:hidden;transition:max-height .35s cubic-bezier(.4,0,.2,1)}
        .hc-faq-a-inner{padding:0 24px 20px;font-size:15px;color:var(--hc-muted);line-height:1.65}
        .hc-faq-item.open .hc-faq-a{max-height:320px}

        .hc-contact{background:linear-gradient(135deg,var(--hc-navy),var(--hc-navy-2));text-align:center;position:relative;overflow:hidden}
        .hc-contact::before{content:'';position:absolute;top:-30%;left:50%;transform:translateX(-50%);width:700px;height:500px;background:radial-gradient(circle,rgba(38,198,218,.08),transparent 70%)}
        .hc-contact-inner{position:relative;z-index:1}
        .hc-contact h2{color:#fff;font-size:clamp(28px,4vw,40px);font-weight:800;margin:0 0 14px;letter-spacing:-.02em}
        .hc-contact p{font-size:19px;color:rgba(255,255,255,.75);margin:0 0 34px}
        .hc-contact .hc-eyebrow{background:rgba(255,255,255,.08);color:#cffafe;border-color:rgba(255,255,255,.2)}
        .hc-contact-link{margin-left:8px}

        .hc-footer{background:var(--hc-navy);color:rgba(255,255,255,.7);padding:56px 0 28px}
        .hc-footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:40px}
        .hc-footer-brand p{font-size:14px;line-height:1.6;margin:14px 0 0;max-width:280px}
        .hc-footer-brand img{height:36px;filter:brightness(0) invert(1)}
        .hc-footer h4{color:#fff;font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;margin:0 0 16px}
        .hc-footer ul{list-style:none;padding:0;margin:0}
        .hc-footer ul li{margin-bottom:10px}
        .hc-footer ul a{color:rgba(255,255,255,.6);font-size:14px}
        .hc-footer ul a:hover{color:#fff}
        .hc-footer-social{display:flex;gap:12px;margin-top:16px}
        .hc-footer-social a{width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.5);font-size:18px;transition:all var(--hc-tr)}
        .hc-footer-social a:hover{background:var(--hc-teal);color:#fff}
        .hc-footer-bottom{border-top:1px solid rgba(255,255,255,.08);margin-top:40px;padding-top:24px;text-align:center;font-size:13px;color:rgba(255,255,255,.4)}

        .hc-modal{position:fixed;inset:0;z-index:100;display:none;align-items:center;justify-content:center;padding:18px;background:rgba(15,23,42,.6);backdrop-filter:blur(4px)}
        .hc-modal.open{display:flex}
        .hc-modal-dialog{width:min(100%,540px);max-height:calc(100vh - 36px);overflow-y:auto;border-radius:18px;background:#fff;box-shadow:var(--hc-shadow-lg)}
        .hc-modal-head{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:22px 24px;border-bottom:1px solid var(--hc-line)}
        .hc-modal-head h3{margin:0;font-size:20px;font-weight:800}
        .hc-modal-close{width:38px;height:38px;border:1px solid var(--hc-line);border-radius:10px;background:#fff;display:flex;align-items:center;justify-content:center;color:var(--hc-muted)}
        .hc-modal-close:hover{background:var(--hc-bg);color:var(--hc-ink)}
        .hc-modal-body{padding:22px 24px}
        .hc-form-group{margin-bottom:16px}
        .hc-form-group label{display:block;font-size:13px;font-weight:700;color:var(--hc-ink);margin-bottom:6px}
        .hc-form-group input,.hc-form-group textarea,.hc-form-group select{width:100%;padding:11px 14px;border:1px solid var(--hc-line);border-radius:10px;font:inherit;font-size:15px;color:var(--hc-ink);background:#fff;transition:border-color var(--hc-tr),box-shadow var(--hc-tr)}
        .hc-form-group input:focus,.hc-form-group textarea:focus,.hc-form-group select:focus{border-color:var(--hc-teal);box-shadow:0 0 0 .2rem rgba(15,118,110,.16);outline:none}
        .hc-form-group textarea{min-height:110px;resize:vertical}
        .hc-form-group .hc-hp{position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden}
        .hc-consent{display:inline-flex;align-items:center;gap:10px;font-size:13px;color:var(--hc-muted);cursor:pointer}
        .hc-consent input{flex-shrink:0;width:16px;height:16px;cursor:pointer}
        .hc-form-feedback{display:none;margin-top:14px;padding:14px 16px;border-radius:10px;font-size:14px;font-weight:600}
        .hc-form-feedback.success{display:block;background:rgba(34,197,94,.10);color:#16a34a}
        .hc-form-feedback.error{display:block;background:rgba(239,68,68,.10);color:#dc2626}
        .hc-form-actions{display:flex;gap:10px;margin-top:18px}
        .hc-form-actions .hc-btn{flex:1}

        .hc-cookie-banner{position:fixed;left:18px;right:auto;bottom:18px;z-index:80;display:none;width:min(calc(100% - 36px),430px);padding:16px;border:1px solid var(--hc-line);border-radius:14px;background:#fff;box-shadow:0 20px 70px rgba(15,23,42,.22)}
        .hc-cookie-banner.show{display:block}
        .hc-cookie-content{display:grid;gap:16px}
        .hc-cookie-content h3{margin:0 0 4px;font-size:17px}
        .hc-cookie-content p{margin:0;color:var(--hc-muted);font-size:13px}
        .hc-cookie-actions{display:flex;flex-wrap:wrap;gap:8px}
        .hc-cookie-actions .hc-btn{min-height:38px;padding:0 14px;font-size:13px}
        .hc-cookie-modal{position:fixed;inset:0;z-index:90;display:none;align-items:center;justify-content:center;padding:18px;background:rgba(15,23,42,.54)}
        .hc-cookie-modal.open{display:flex}
        .hc-cookie-dialog{width:min(100%,560px);border-radius:14px;background:#fff;box-shadow:var(--hc-shadow);overflow:hidden}
        .hc-cookie-head{display:flex;justify-content:space-between;align-items:center;gap:14px;padding:18px 20px;border-bottom:1px solid var(--hc-line)}
        .hc-cookie-head h3{margin:0}
        .hc-icon-btn{width:38px;height:38px;border:1px solid var(--hc-line);border-radius:10px;background:#fff;display:flex;align-items:center;justify-content:center}
        .hc-cookie-body{padding:6px 20px 20px}
        .hc-cookie-row{display:grid;grid-template-columns:1fr auto;gap:18px;align-items:center;padding:16px 0;border-bottom:1px solid var(--hc-line)}
        .hc-cookie-row:last-of-type{border-bottom:0}
        .hc-cookie-row h4{margin:0 0 3px}
        .hc-cookie-row p{margin:0;color:var(--hc-muted);font-size:14px}
        .hc-switch{position:relative;width:48px;height:28px;display:inline-block}
        .hc-switch input{opacity:0;width:0;height:0}
        .hc-slider{position:absolute;inset:0;border-radius:999px;background:#cbd5e1;cursor:pointer;transition:background var(--hc-tr)}
        .hc-slider:before{content:"";position:absolute;width:22px;height:22px;left:3px;top:3px;border-radius:50%;background:#fff;transition:transform var(--hc-tr)}
        .hc-switch input:checked + .hc-slider{background:var(--hc-teal)}
        .hc-switch input:checked + .hc-slider:before{transform:translateX(20px)}
        .hc-switch input:disabled + .hc-slider{background:#94a3b8;cursor:not-allowed}

        .hc-animate{opacity:0}
        .hc-animate.in{animation:hcFadeUp .6s cubic-bezier(.4,0,.2,1) forwards}
        @keyframes hcFadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}

        @media (max-width:1080px){
            .hc-nav-links{display:none}
            .hc-nav-toggle{display:inline-flex}
            .hc-hero-grid,.hc-highlight-grid{grid-template-columns:1fr;gap:40px}
            .hc-hero-mock{max-width:480px}
            .hc-features-grid{grid-template-columns:repeat(2,1fr)}
            .hc-pricing-grid,.hc-steps{grid-template-columns:1fr;max-width:420px;margin-inline:auto}
        }
        @media (max-width:760px){
            body *{min-width:0}
            .hc-container{width:min(100% - 24px,var(--hc-max))}
            .hc-nav-inner{gap:8px;min-width:0}
            .hc-nav-actions{gap:6px;min-width:0}
            .hc-nav-actions .hc-btn-primary{min-height:38px;padding:0 12px;font-size:13px}
            .hc-nav-actions .hc-btn-ghost{display:none}
            .hc-lang{flex-shrink:0}
            .hc-lang a{min-width:32px;padding:6px 8px}
            .hc-hero{padding:118px 0 76px}
            .hc-hero h1,.hc-hero p,.hc-section h2,.hc-price-tag{overflow-wrap:anywhere}
            .hc-hero-grid,.hc-highlight-grid,.hc-features-grid,.hc-security-grid,.hc-footer-grid,.hc-pricing-grid,.hc-steps{min-width:0;width:100%;max-width:100%}
            .hc-hero-mock,.hc-hero-card,.hc-highlight-visual,.hc-hl-card,.hc-feature-card,.hc-price-card,.hc-sec-card,.hc-testimonial,.hc-faq-list,.hc-contact-inner{width:100%;max-width:100%;min-width:0}
            .hc-hero-card,.hc-hero-card:hover{transform:none}
            .hc-mock-row,.hc-hl-card-head,.hc-hl-card-foot,.hc-price-features li,.hc-highlight-list li{min-width:0}
            .hc-hero-cta{flex-direction:column}
            .hc-hero-cta .hc-btn{width:100%}
            .hc-stats-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
            .hc-stat{padding:14px 10px;border:1px solid var(--hc-line);border-radius:12px;background:#fff;box-shadow:0 8px 22px rgba(15,23,42,.05)}
            .hc-stat-icon{width:40px;height:40px;border-radius:10px;font-size:21px;margin-bottom:8px}
            .hc-stat h3{font-size:26px;line-height:1.05}
            .hc-stat p{font-size:12px;line-height:1.35;margin-top:5px}
            .hc-steps{grid-template-columns:1fr;gap:36px}
            .hc-security-grid,.hc-footer-grid{grid-template-columns:1fr}
            .hc-features-grid{grid-template-columns:1fr}
            .hc-section{padding:58px 0}
            .hc-stats-section{padding:34px 0!important}
            .hc-cookie-banner{left:12px;right:12px;bottom:12px;width:auto;padding:12px;border-radius:12px}
            .hc-cookie-content{gap:10px}
            .hc-cookie-content h3{font-size:15px}
            .hc-cookie-content p{font-size:11px;line-height:1.35}
            .hc-cookie-actions{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:6px}
            .hc-cookie-actions .hc-btn{min-height:34px;padding:0 8px;font-size:11px;white-space:normal}
            .hc-contact-link{display:inline-flex;margin-top:12px;margin-left:0}
        }
        @media (max-width:420px){
            .hc-brand img{height:28px}
            .hc-nav-actions .hc-btn-primary{display:none}
            .hc-stats-grid{gap:10px}
            .hc-stat{padding:12px 8px}
            .hc-stat-icon{width:34px;height:34px;font-size:18px;margin-bottom:7px}
            .hc-stat h3{font-size:22px}
            .hc-stat p{font-size:11px}
        }
    </style>
</head>
<body>

<nav class="hc-nav" id="hcNav" aria-label="Main navigation">
    <div class="hc-container">
        <div class="hc-nav-inner">
            <a href="{{ $homeUrl }}" class="hc-brand">
                <img src="{{ asset('images/logo-full.png') }}" alt="{{ $appName }} logo">
            </a>
            <ul class="hc-nav-links">
                <li><a href="#features">{{ $t['nav']['features'] }}</a></li>
                <li><a href="#workflow">{{ $t['nav']['workflow'] }}</a></li>
                <li><a href="#security">{{ $t['nav']['security'] }}</a></li>
                <li><a href="#pricing">{{ $t['nav']['pricing'] }}</a></li>
                <li><a href="#faq">{{ $t['nav']['faq'] }}</a></li>
                <li><a href="#contact">{{ $t['nav']['contact'] }}</a></li>
            </ul>
            <div class="hc-nav-actions">
                <div class="hc-lang" aria-label="Language switcher">
                    <a href="{{ $languageSwitchUrls['en'] }}" class="{{ $language === 'en' ? 'active' : '' }}" hreflang="en">EN</a>
                    <a href="{{ $languageSwitchUrls['id'] }}" class="{{ $language === 'id' ? 'active' : '' }}" hreflang="id">ID</a>
                </div>
                <a class="hc-btn hc-btn-ghost" href="{{ $loginUrl }}">{{ $t['nav']['login'] }}</a>
                <a class="hc-btn hc-btn-primary" href="{{ $loginUrl }}"><span class="mdi mdi-arrow-right"></span>{{ $t['hero']['primary'] }}</a>
                <button class="hc-nav-toggle" id="hcNavToggle" type="button" aria-label="Open menu">
                    <span class="mdi mdi-menu"></span>
                </button>
            </div>
        </div>
        <div class="hc-container">
            <div class="hc-mobile" id="hcMobile">
                <a href="#features">{{ $t['nav']['features'] }}</a>
                <a href="#workflow">{{ $t['nav']['workflow'] }}</a>
                <a href="#security">{{ $t['nav']['security'] }}</a>
                <a href="#pricing">{{ $t['nav']['pricing'] }}</a>
                <a href="#faq">{{ $t['nav']['faq'] }}</a>
                <a href="#contact">{{ $t['nav']['contact'] }}</a>
                <a href="{{ $loginUrl }}">{{ $t['nav']['login'] }}</a>
            </div>
        </div>
    </div>
</nav>

<section class="hc-hero">
    <div class="hc-container">
        <div class="hc-hero-grid">
            <div class="hc-hero-copy">
                <div class="hc-hero-eyebrow"><span class="mdi mdi-qrcode-scan"></span>{{ $t['hero']['eyebrow'] }}</div>
                <h1>{!! collect(explode(' ', $t['hero']['title']))->take(2)->implode(' ') !!} <span class="hc-accent">{{ collect(explode(' ', $t['hero']['title']))->skip(2)->implode(' ') }}</span></h1>
                <p>{{ $t['hero']['subtitle'] }}</p>
                <div class="hc-hero-cta">
                    <a class="hc-btn hc-btn-primary hc-btn-lg" href="#pricing"><span class="mdi mdi-rocket"></span>{{ $t['hero']['primary'] }}</a>
                    <a class="hc-btn hc-btn-light hc-btn-lg" href="{{ $loginUrl }}"><span class="mdi mdi-login"></span>{{ $t['hero']['secondary'] }}</a>
                </div>
                <div class="hc-hero-trust">
                    <span class="mdi mdi-check-circle"></span>
                    <span>{{ $t['hero']['trust'] }} - <a href="https://rekayasadigital.com" rel="noopener" style="color:#fff;text-decoration:underline">rekayasadigital.com</a></span>
                </div>
            </div>
            <div class="hc-hero-mock">
                <div class="hc-hero-card">
                    <div class="hc-hero-card-top">
                        <span class="hc-dot r"></span><span class="hc-dot y"></span><span class="hc-dot g"></span>
                        <b>{{ $appName }} &middot; Live Dashboard</b>
                    </div>
                    <div class="hc-hero-card-body">
                        <div class="hc-mock-row">
                            <div class="hc-mock-label"><div class="hc-mock-icon hc-fic-teal"><span class="mdi mdi-account-multiple"></span></div>Grand Ballroom — Plenary</div>
                            <span class="hc-mock-value hc-badge-live"><span class="mdi mdi-record"></span>247 live</span>
                        </div>
                        <div class="hc-mock-row">
                            <div class="hc-mock-label"><div class="hc-mock-icon hc-fic-orange"><span class="mdi mdi-silverware-fork"></span></div>Lunch &middot; Package A</div>
                            <span class="hc-mock-value hc-badge-orange">186 / 250</span>
                        </div>
                        <div class="hc-mock-row">
                            <div class="hc-mock-label"><div class="hc-mock-icon hc-fic-indigo"><span class="mdi mdi-qrcode"></span></div>QR Token — Active</div>
                            <span class="hc-mock-value hc-badge-teal">Rotating</span>
                        </div>
                        <div class="hc-mock-row">
                            <div class="hc-mock-label"><div class="hc-mock-icon hc-fic-green"><span class="mdi mdi-check-circle"></span></div>Registration Station</div>
                            <span class="hc-mock-value hc-badge-live"><span class="mdi mdi-record"></span>Online</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="hc-section alt hc-stats-section hc-animate" style="padding:64px 0">
    <div class="hc-container">
        <div class="hc-stats-grid">
            @foreach ($t['stats'] as $stat)
                <div class="hc-stat hc-animate">
                    <div class="hc-stat-icon"><span class="mdi {{ $stat['icon'] }}"></span></div>
                    <h3>{{ $stat['value'] }}</h3>
                    <p>{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="hc-section" id="features">
    <div class="hc-container">
        <div class="hc-section-head hc-center hc-animate">
            <span class="hc-eyebrow"><span class="mdi mdi-star"></span>{{ $t['featuresEyebrow'] }}</span>
            <h2>{{ $t['featuresTitle'] }}</h2>
            <p>{{ $t['featuresSub'] }}</p>
        </div>
        <div class="hc-features-grid">
            @foreach ($t['features'] as $feature)
                <article class="hc-feature-card hc-animate">
                    <div class="hc-feature-icon"><span class="mdi {{ $feature['icon'] }}"></span></div>
                    <h3>{{ $feature['title'] }}</h3>
                    <p>{{ $feature['body'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="hc-section alt" id="workflow">
    <div class="hc-container">
        <div class="hc-section-head hc-center hc-animate">
            <span class="hc-eyebrow"><span class="mdi mdi-source-branch"></span>{{ $t['stepsEyebrow'] }}</span>
            <h2>{{ $t['stepsTitle'] }}</h2>
            <p>{{ $t['stepsSub'] }}</p>
        </div>
        <div class="hc-steps">
            @foreach ($t['steps'] as $index => $step)
                <div class="hc-step hc-animate">
                    <div class="hc-step-num">{{ $index + 1 }}</div>
                    <h3>{{ $step['title'] }}</h3>
                    <p>{{ $step['body'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="hc-section">
    <div class="hc-container">
        <div class="hc-highlight-grid">
            <div class="hc-highlight-visual hc-animate">
                <div class="hc-hl-card">
                    <div class="hc-hl-card-head"><b>Grand Ballroom — Check-in</b><span class="hc-mock-value hc-badge-live"><span class="mdi mdi-record"></span>Live</span></div>
                    <div class="hc-hl-bar"><div class="hc-hl-bar-fill hc-bar-teal" style="width:92%"></div></div>
                    <div class="hc-hl-card-foot"><span>236 / 250 registered</span><span>95%</span></div>
                </div>
                <div class="hc-hl-card">
                    <div class="hc-hl-card-head"><b>Lunch Package B — Entitlements</b><span class="hc-mock-value hc-badge-orange">180 / 200</span></div>
                    <div class="hc-hl-bar"><div class="hc-hl-bar-fill hc-bar-orange" style="width:75%"></div></div>
                    <div class="hc-hl-card-foot"><span>Redeemed</span><span>90%</span></div>
                </div>
                <div class="hc-hl-card">
                    <div class="hc-hl-card-head"><b>Floor 3 Breakout — Occupancy</b><span class="hc-mock-value hc-badge-teal">64 / 80</span></div>
                    <div class="hc-hl-bar"><div class="hc-hl-bar-fill hc-bar-indigo" style="width:80%"></div></div>
                    <div class="hc-hl-card-foot"><span>Capacity</span><span>80%</span></div>
                </div>
            </div>
            <div class="hc-animate">
                <span class="hc-eyebrow"><span class="mdi mdi-view-dashboard"></span>{{ $t['highlightEyebrow'] }}</span>
                <h2 class="hc-section-title" style="font-size:36px;font-weight:800;line-height:1.18;margin:22px 0 14px">{{ $t['highlightTitle'] }}</h2>
                <p style="font-size:18px;color:var(--hc-muted);line-height:1.7;max-width:560px;margin:0 0 8px">{{ $t['highlightSub'] }}</p>
                <ul class="hc-highlight-list">
                    @foreach ($t['highlightList'] as $item)
                        <li><span class="mdi mdi-check"></span>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="hc-section alt" id="security">
    <div class="hc-container">
        <div class="hc-section-head hc-center hc-animate">
            <span class="hc-eyebrow"><span class="mdi mdi-security"></span>{{ $t['securityEyebrow'] }}</span>
            <h2>{{ $t['securityTitle'] }}</h2>
            <p>{{ $t['securitySub'] }}</p>
        </div>
        <div class="hc-security-grid">
            @foreach ($t['security'] as $item)
                <article class="hc-sec-card hc-animate">
                    <div class="hc-sec-icon"><span class="mdi {{ $item['icon'] }}"></span></div>
                    <div>
                        <h3>{{ $item['title'] }}</h3>
                        <p>{{ $item['body'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="hc-section" id="pricing">
    <div class="hc-container">
        <div class="hc-section-head hc-center hc-animate">
            <span class="hc-eyebrow"><span class="mdi mdi-tag"></span>{{ $t['pricingEyebrow'] }}</span>
            <h2>{{ $t['pricingTitle'] }}</h2>
            <p>{{ $t['pricingSub'] }}</p>
        </div>
        <div class="hc-pricing-grid">
            @foreach ($t['plans'] as $plan)
                <article class="hc-price-card hc-animate {{ $loop->iteration === 2 ? 'hc-price-featured' : '' }}">
                    <span class="hc-price-badge">{{ $plan['badge'] }}</span>
                    <h3>{{ $plan['name'] }}</h3>
                    <div class="hc-price-tag">{{ $plan['price'] }}@if($plan['key'] !== 'enterprise') <span>/month</span>@endif</div>
                    @isset($plan['userLimit'])<div class="hc-price-userlimit"><span class="mdi mdi-account-multiple"></span>{{ $plan['userLimit'] }}</div>@endisset
                    <p class="hc-price-desc">{{ $plan['desc'] }}</p>
                    <ul class="hc-price-features">
                        @foreach ($plan['items'] as $item)
                            <li><span class="mdi mdi-check"></span>{{ $item }}</li>
                        @endforeach
                    </ul>
                    @if ($plan['cta'] === 'register')
                        <button type="button" class="hc-btn {{ $loop->iteration === 2 ? 'hc-btn-primary' : 'hc-btn-ghost' }}" data-register-plan="{{ $plan['key'] }}" data-plan-name="{{ $plan['name'] }}">
                            <span class="mdi mdi-account-plus"></span>{{ $t['hero']['primary'] }}
                        </button>
                    @else
                        <button type="button" class="hc-btn hc-btn-ghost" data-contact-plan="{{ $plan['key'] }}" data-plan-name="{{ $plan['name'] }}">
                            <span class="mdi mdi-email"></span>Contact Sales
                        </button>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="hc-section alt">
    <div class="hc-container">
        <div class="hc-testimonial hc-animate">
            <span class="mdi mdi-format-quote" style="font-size:40px;color:rgba(20,184,166,.2)"></span>
            <blockquote>"{{ $t['testimonial']['quote'] }}"</blockquote>
            <div class="hc-testi-author">
                <div class="hc-testi-avatar">GM</div>
                <div style="text-align:left">
                    <b>{{ $t['testimonial']['name'] }}</b>
                    <span>{{ $t['testimonial']['role'] }}</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="hc-section" id="faq">
    <div class="hc-container">
        <div class="hc-section-head hc-center hc-animate">
            <span class="hc-eyebrow"><span class="mdi mdi-help-circle"></span>{{ $t['faqEyebrow'] }}</span>
            <h2>{{ $t['faqTitle'] }}</h2>
            <p>{{ $t['faqSub'] }}</p>
        </div>
        <div class="hc-faq-list hc-animate">
            @foreach ($t['faqs'] as $faq)
                <div class="hc-faq-item">
                    <button class="hc-faq-q" type="button">
                        <span>{{ $faq['q'] }}</span>
                        <span class="mdi mdi-chevron-down"></span>
                    </button>
                    <div class="hc-faq-a"><div class="hc-faq-a-inner">{{ $faq['a'] }}</div></div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="hc-section hc-contact" id="contact">
    <div class="hc-container">
        <div class="hc-contact-inner hc-animate">
            <span class="hc-eyebrow"><span class="mdi mdi-email"></span>{{ $t['contactEyebrow'] }}</span>
            <h2>{{ $t['contactTitle'] }}</h2>
            <p>{{ $t['contactSub'] }}</p>
            <button class="hc-btn hc-btn-primary hc-btn-lg" data-open-contact><span class="mdi mdi-email"></span>{{ $contactEmail }}</button>
            <a class="hc-btn hc-btn-light hc-btn-lg hc-contact-link" href="https://rekayasadigital.com" rel="noopener"><span class="mdi mdi-web"></span>rekayasadigital.com</a>
        </div>
    </div>
</section>

<footer class="hc-footer">
    <div class="hc-container">
        <div class="hc-footer-grid">
            <div class="hc-footer-brand">
                <img src="{{ asset('images/logo-full.png') }}" alt="{{ $appName }} logo">
                <p>{{ $t['footerText'] }}</p>
                <p>{{ $t['builtBy'] }} <a href="https://rekayasadigital.com" rel="noopener">Rekayasa Digital</a>.</p>
                <div class="hc-footer-social">
                    <a href="#" aria-label="Twitter"><span class="mdi mdi-twitter"></span></a>
                    <a href="#" aria-label="LinkedIn"><span class="mdi mdi-linkedin"></span></a>
                    <a href="#" aria-label="Facebook"><span class="mdi mdi-facebook"></span></a>
                    <a href="#" aria-label="Instagram"><span class="mdi mdi-instagram"></span></a>
                </div>
            </div>
            <div>
                <h4>{{ $t['footer']['product'] }}</h4>
                <ul>
                    <li><a href="#features">{{ $t['footer']['features'] }}</a></li>
                    <li><a href="#workflow">{{ $t['footer']['workflow'] }}</a></li>
                    <li><a href="#security">{{ $t['footer']['security'] }}</a></li>
                    <li><a href="#pricing">{{ $t['footer']['pricing'] }}</a></li>
                    <li><a href="#faq">{{ $t['footer']['faq'] }}</a></li>
                </ul>
            </div>
            <div>
                <h4>{{ $t['footer']['company'] }}</h4>
                <ul>
                    <li><a href="https://rekayasadigital.com" rel="noopener">{{ $t['footer']['about'] }}</a></li>
                    <li><a href="#contact" data-open-contact>{{ $t['footer']['contact'] }}</a></li>
                    <li><a href="{{ $loginUrl }}">{{ $t['footer']['login'] }}</a></li>
                </ul>
            </div>
            <div>
                <h4>{{ $t['footer']['resources'] }}</h4>
                <ul>
                    <li><a href="{{ route('sitemap') }}">{{ $t['footer']['sitemap'] }}</a></li>
                    <li><a href="#" data-open-contact>{{ $contactEmail }}</a></li>
                </ul>
            </div>
        </div>
        <div class="hc-footer-bottom">
            &copy; {{ date('Y') }} {{ $appName }}. {{ $t['builtBy'] }} Rekayasa Digital. {{ $canonicalUrl }}
        </div>
    </div>
</footer>

<div class="hc-modal" id="hcModal" aria-hidden="true">
    <div class="hc-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="hcModalTitle">
        <div class="hc-modal-head">
            <h3 id="hcModalTitle">{{ $t['contactModalTitle'] }}</h3>
            <button class="hc-modal-close" data-modal-close type="button" aria-label="{{ $t['forms']['close'] }}"><span class="mdi mdi-close"></span></button>
        </div>
        <div class="hc-modal-body">
            <form id="hcInquiryForm" data-type="contact">
                @csrf
                <input type="hidden" name="type" value="contact">
                <input type="hidden" name="plan" value="general">
                <input type="hidden" name="plan_label" value="">
                <div class="hc-form-group hc-hp" aria-hidden="true">
                    <label>Leave this empty</label>
                    <input type="text" name="hp_field" tabindex="-1" autocomplete="off">
                </div>
                <div class="hc-form-group">
                    <label for="hcName">{{ $t['forms']['name'] }} *</label>
                    <input type="text" id="hcName" name="name" required maxlength="120">
                </div>
                <div class="hc-form-group">
                    <label for="hcEmail">{{ $t['forms']['email'] }} *</label>
                    <input type="email" id="hcEmail" name="email" required maxlength="150">
                </div>
                <div class="hc-form-group">
                    <label for="hcHotel">{{ $t['forms']['hotel'] }}</label>
                    <input type="text" id="hcHotel" name="hotel" maxlength="150">
                </div>
                <div class="hc-form-group">
                    <label for="hcSubject">{{ $t['forms']['subject'] }} *</label>
                    <input type="text" id="hcSubject" name="subject" required maxlength="200">
                </div>
                <div class="hc-form-group">
                    <label for="hcMessage">{{ $t['forms']['message'] }} *</label>
                    <textarea id="hcMessage" name="message" required minlength="10" maxlength="2000"></textarea>
                </div>
                <div class="hc-form-group">
                    <label class="hc-consent">
                        <input type="checkbox" name="consent" value="1" required>
                        <span>{{ $t['forms']['consent'] }}</span>
                    </label>
                </div>
                <div class="hc-form-feedback" id="hcFormFeedback"></div>
                <div class="hc-form-actions">
                    <button type="button" class="hc-btn hc-btn-ghost" data-modal-close>{{ $t['forms']['close'] }}</button>
                    <button type="submit" class="hc-btn hc-btn-primary" id="hcSubmitBtn"><span class="mdi mdi-send"></span>{{ $t['forms']['send'] }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="hc-cookie-banner" id="hcCookieBanner" role="dialog" aria-live="polite" aria-label="{{ $t['cookie']['title'] }}">
    <div class="hc-cookie-content">
        <div>
            <h3>{{ $t['cookie']['title'] }}</h3>
            <p>{{ $t['cookie']['body'] }}</p>
        </div>
        <div class="hc-cookie-actions">
            <button class="hc-btn hc-btn-ghost" type="button" data-cookie-settings>{{ $t['cookie']['settings'] }}</button>
            <button class="hc-btn hc-btn-ghost" type="button" data-cookie-reject>{{ $t['cookie']['reject'] }}</button>
            <button class="hc-btn hc-btn-primary" type="button" data-cookie-accept>{{ $t['cookie']['accept'] }}</button>
        </div>
    </div>
</div>

<div class="hc-cookie-modal" id="hcCookieModal" aria-hidden="true">
    <div class="hc-cookie-dialog" role="dialog" aria-modal="true" aria-labelledby="hcCookieModalTitle">
        <div class="hc-cookie-head">
            <h3 id="hcCookieModalTitle">{{ $t['cookie']['title'] }}</h3>
            <button class="hc-icon-btn" type="button" data-cookie-close aria-label="{{ $t['forms']['close'] }}"><span class="mdi mdi-close"></span></button>
        </div>
        <div class="hc-cookie-body">
            <div class="hc-cookie-row">
                <div>
                    <h4>{{ $t['cookie']['necessary'] }}</h4>
                    <p>{{ $t['cookie']['necessaryBody'] }}</p>
                </div>
                <label class="hc-switch"><input type="checkbox" checked disabled><span class="hc-slider"></span></label>
            </div>
            <div class="hc-cookie-row">
                <div>
                    <h4>{{ $t['cookie']['analytics'] }}</h4>
                    <p>{{ $t['cookie']['analyticsBody'] }}</p>
                </div>
                <label class="hc-switch"><input type="checkbox" id="hcCookieAnalytics"><span class="hc-slider"></span></label>
            </div>
            <div class="hc-cookie-row">
                <div>
                    <h4>{{ $t['cookie']['marketing'] }}</h4>
                    <p>{{ $t['cookie']['marketingBody'] }}</p>
                </div>
                <label class="hc-switch"><input type="checkbox" id="hcCookieMarketing"><span class="hc-slider"></span></label>
            </div>
            <div class="hc-cookie-actions" style="display:flex;gap:8px;margin-top:18px">
                <button class="hc-btn hc-btn-ghost" type="button" data-cookie-reject>{{ $t['cookie']['reject'] }}</button>
                <button class="hc-btn hc-btn-primary" type="button" data-cookie-save>{{ $t['cookie']['save'] }}</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var nav = document.getElementById('hcNav');
        var toggle = document.getElementById('hcNavToggle');
        var mobile = document.getElementById('hcMobile');
        window.addEventListener('scroll', function () { nav.classList.toggle('scrolled', window.scrollY > 12); });
        if (toggle && mobile) {
            toggle.addEventListener('click', function () { mobile.classList.toggle('open'); });
            mobile.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', function () { mobile.classList.remove('open'); }); });
        }
        document.querySelectorAll('.hc-faq-q').forEach(function (b) {
            b.addEventListener('click', function () {
                var item = b.closest('.hc-faq-item');
                var wasOpen = item.classList.contains('open');
                document.querySelectorAll('.hc-faq-item').forEach(function (e) { e.classList.remove('open'); });
                if (!wasOpen) item.classList.add('open');
            });
        });
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) { entry.target.classList.add('in'); observer.unobserve(entry.target); }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
        document.querySelectorAll('.hc-animate').forEach(function (el) { observer.observe(el); });
    })();

    (function () {
        var modal = document.getElementById('hcModal');
        var form = document.getElementById('hcInquiryForm');
        var title = document.getElementById('hcModalTitle');
        var feedback = document.getElementById('hcFormFeedback');
        var submitBtn = document.getElementById('hcSubmitBtn');
        var submitLabel = submitBtn.innerHTML;
        var i18n = @json($jsI18n);

        function openModal(type, planKey, planName) {
            form.dataset.type = type;
            form.querySelector('input[name="type"]').value = type;
            form.querySelector('input[name="plan"]').value = planKey || 'general';
            form.querySelector('input[name="plan_label"]').value = planName || '';
            title.textContent = type === 'register' ? i18n.registerModalTitle : i18n.contactModalTitle;
            submitBtn.innerHTML = '<span class="mdi mdi-send"></span>' + (type === 'register' ? i18n.register : i18n.send);
            feedback.className = 'hc-form-feedback';
            feedback.textContent = '';
            form.reset();
            form.querySelector('input[name="type"]').value = type;
            form.querySelector('input[name="plan"]').value = planKey || 'general';
            form.querySelector('input[name="plan_label"]').value = planName || '';
            if (type === 'register') {
                var msg = document.getElementById('hcMessage');
                if (msg) msg.value = 'I would like to register for the ' + (planName || '') + ' plan.';
                var subj = document.getElementById('hcSubject');
                if (subj) subj.value = 'Registration request - ' + (planName || 'Plan');
            }
            modal.classList.add('open');
            modal.setAttribute('aria-hidden', 'false');
        }
        function closeModal() {
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
        }
        document.querySelectorAll('[data-open-contact]').forEach(function (b) { b.addEventListener('click', function () { openModal('contact', 'general', ''); }); });
        document.querySelectorAll('[data-register-plan]').forEach(function (b) { b.addEventListener('click', function () { openModal('register', b.dataset.registerPlan, b.dataset.planName); }); });
        document.querySelectorAll('[data-contact-plan]').forEach(function (b) { b.addEventListener('click', function () { openModal('contact', b.dataset.contactPlan, b.dataset.planName); }); });
        document.querySelectorAll('[data-modal-close]').forEach(function (b) { b.addEventListener('click', closeModal); });
        modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            feedback.className = 'hc-form-feedback';
            feedback.textContent = '';
            if (!form.checkValidity()) {
                feedback.className = 'hc-form-feedback error';
                feedback.textContent = i18n.validationError;
                return;
            }
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="mdi mdi-refresh"></span>' + i18n.sending;
            var data = new FormData(form);
            var url = form.dataset.type === 'register' ? '{{ route('landing.register') }}' : '{{ route('landing.contact') }}';
            fetch(url, { method: 'POST', body: data, headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                .then(function (r) { return r.json().then(function (body) { return { ok: r.ok, body: body }; }); })
                .then(function (res) {
                    var type = form.dataset.type;
                    feedback.className = 'hc-form-feedback ' + (res.ok ? 'success' : 'error');
                    feedback.textContent = res.ok ? (type === 'register' ? i18n.successRegister : i18n.successContact) : (res.body.message || i18n.error);
                    if (res.ok) form.reset();
                })
                .catch(function () {
                    feedback.className = 'hc-form-feedback error';
                    feedback.textContent = i18n.error;
                })
                .finally(function () {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = submitLabel;
                    submitBtn.innerHTML = '<span class="mdi mdi-send"></span>' + (form.dataset.type === 'register' ? i18n.register : i18n.send);
                });
        });
    })();

    (function () {
        var consentName = 'headcounter_cookie_preferences';
        var banner = document.getElementById('hcCookieBanner');
        var modal = document.getElementById('hcCookieModal');
        var analytics = document.getElementById('hcCookieAnalytics');
        var marketing = document.getElementById('hcCookieMarketing');
        function setCookie(name, value, days) {
            var expires = new Date(Date.now() + days * 864e5).toUTCString();
            var secure = location.protocol === 'https:' ? '; Secure' : '';
            document.cookie = name + '=' + encodeURIComponent(value) + '; Expires=' + expires + '; Path=/; SameSite=Lax' + secure;
        }
        function getCookie(name) {
            return document.cookie.split('; ').reduce(function (found, pair) {
                if (found) return found;
                var parts = pair.split('=');
                return parts[0] === name ? decodeURIComponent(parts.slice(1).join('=')) : '';
            }, '');
        }
        function readPreferences() { try { return JSON.parse(getCookie(consentName)); } catch (e) { return null; } }
        function savePreferences(p) { p.necessary = true; p.savedAt = new Date().toISOString(); setCookie(consentName, JSON.stringify(p), 365); applyPreferences(p); banner.classList.remove('show'); modal.classList.remove('open'); modal.setAttribute('aria-hidden', 'true'); }
        function applyPreferences(p) { window.headCounterCookieConsent = p; document.documentElement.dataset.analyticsCookies = p.analytics ? 'enabled' : 'disabled'; document.documentElement.dataset.marketingCookies = p.marketing ? 'enabled' : 'disabled'; }
        function openSettings() { var p = readPreferences() || { analytics: false, marketing: false }; analytics.checked = !!p.analytics; marketing.checked = !!p.marketing; modal.classList.add('open'); modal.setAttribute('aria-hidden', 'false'); }
        var saved = readPreferences();
        if (saved) { applyPreferences(saved); } else { banner.classList.add('show'); }
        document.querySelectorAll('[data-cookie-accept]').forEach(function (b) { b.addEventListener('click', function () { savePreferences({ analytics: true, marketing: true }); }); });
        document.querySelectorAll('[data-cookie-reject]').forEach(function (b) { b.addEventListener('click', function () { savePreferences({ analytics: false, marketing: false }); }); });
        document.querySelectorAll('[data-cookie-settings]').forEach(function (b) { b.addEventListener('click', openSettings); });
        document.querySelectorAll('[data-cookie-close]').forEach(function (b) { b.addEventListener('click', function () { modal.classList.remove('open'); modal.setAttribute('aria-hidden', 'true'); }); });
        document.querySelectorAll('[data-cookie-save]').forEach(function (b) { b.addEventListener('click', function () { savePreferences({ analytics: analytics.checked, marketing: marketing.checked }); }); });
        modal.addEventListener('click', function (e) { if (e.target === modal) { modal.classList.remove('open'); modal.setAttribute('aria-hidden', 'true'); } });
    })();
</script>
</body>
</html>
