<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once "functions.php";

$base_url    = rtrim(getSetting('base_url'), '/') . '/';
$isLoggedIn  = isset($_SESSION['user_id']);
$username    = $isLoggedIn && isset($_SESSION['username']) ? $_SESSION['username'] : '';
$logo_path   = $base_url . "assets/logo.png";

// --- WhatsApp Floating Chat Variables ---
$admin_number = getSetting('admin_number'); // Should be 923XXXXXXXXX (no +)
$wa_link = "https://wa.me/+" . $admin_number . "?text=" . urlencode("Hello, I would like to know more about apiflair. Can you help?");
$wa_img = $base_url . "assets/whatsapp.png";
$wa_chat_texts = [
    "Need help?"
];
?>

<nav class="bg-white shadow-sm relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="<?= $base_url ?>" class="flex items-center space-x-2">
                    <img src="<?= htmlspecialchars($logo_path) ?>" alt="WhatsApp API Logo" class="h-8 w-auto">
                </a>
            </div>

            <!-- Desktop Menu (hidden on small screens) -->
            <div class="hidden md:flex items-center space-x-4">
               <a href="<?= $base_url ?>pricing" class="px-4 py-2 text-gray-700 hover:text-indigo-700  transition">
                   Pricing
                </a>
                <a href="<?= $base_url ?>contact" class="px-4 py-2 text-gray-700 hover:text-indigo-700  transition">
                    Contact
                </a>
                
                <?php if ($isLoggedIn): ?>
                    <span class="text-gray-700 font-medium">Hello, <?= htmlspecialchars($username) ?></span>
                    <a href="<?= $base_url ?>account" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                        Your account
                    </a>
                    <a href="<?= $base_url ?>logout" class="px-4 py-2 text-gray-600 hover:text-indigo-600 transition">
                        Logout
                    </a>
                <?php else: ?>
                    <a href="<?= $base_url ?>login" class="px-4 py-2 text-indigo-600 hover:text-indigo-800 transition">
                        Login
                    </a>
                    <a href="<?= $base_url ?>register" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                        Register
                    </a>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Button (visible on small screens) -->
            <div class="md:hidden flex items-center">
                <button id="mobile-menu-button" class="text-gray-700 focus:outline-none">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Panel (always in DOM; animate opacity & translateY) -->
    <div
        id="mobile-menu"
        class="absolute left-0 right-0 top-full bg-white border-t border-gray-200 shadow-md
               opacity-0 -translate-y-2 pointer-events-none transition-all duration-300 ease-in-out z-50"
    >
        <div class="px-4 pt-4 pb-4 space-y-2">
            <a href="<?= $base_url ?>pricing"
               class="block px-4 py-2 text-gray-700 hover:text-indigo-700  transition">
                Pricing
            </a>
            <a href="<?= $base_url ?>contact"
               class="block px-4 py-2 text-gray-700 hover:text-indigo-700  transition">
                Contact
            </a>
            <?php if ($isLoggedIn): ?>
                <div class="text-gray-700 font-medium">Hello, <?= htmlspecialchars($username) ?></div>
                <a href="<?= $base_url ?>account"
                   class="block px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                    Your account
                </a>
                <a href="<?= $base_url ?>logout"
                   class="block px-4 py-2 text-gray-600 hover:text-indigo-600 transition">
                    Logout
                </a>
            <?php else: ?>
                <a href="<?= $base_url ?>login"
                   class="block px-4 py-2 text-indigo-600 hover:text-indigo-800 transition">
                    Login
                </a>
                <a href="<?= $base_url ?>register"
                   class="block px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                    Register
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- WhatsApp Floating Chat Button (bottom-left corner) -->
<style>
    #wa-float {
        position: fixed;
        left: 1.5rem;
        bottom: 1.5rem;
        z-index: 1000;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        user-select: none;
        transition: box-shadow 0.2s;
    }
    #wa-float:hover {
        box-shadow: 0 8px 24px rgba(37, 211, 102, 0.22);
        transform: scale(1.04);
    }
    #wa-float-img {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        box-shadow: 0 2px 8px rgba(37,211,102,0.14);
        border: 2.5px solid #25d366;
        background: #fff;
        object-fit: cover;
        padding: 2px;
    }
    #wa-float-text {
        background: #25d366;
        color: #fff;
        padding: 0.6rem 1rem 0.6rem 1rem;
        border-radius: 18px 18px 18px 4px;
        font-size: 1.05rem;
        font-weight: 600;
        min-width: 110px;
        letter-spacing: 0.02em;
        box-shadow: 0 1px 6px rgba(37,211,102,0.07);
        transition: background 0.15s;
        white-space: nowrap;
        animation: fadeIn 0.7s;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(18px);}
        to { opacity: 1; transform: translateY(0);}
    }
    @media (max-width: 640px) {
        #wa-float {
            left: 0.6rem;
            bottom: 0.6rem;
        }
        #wa-float-img {
            width: 44px;
            height: 44px;
        }
        #wa-float-text {
            font-size: 0.94rem;
            min-width: 80px;
            padding: 0.45rem 0.8rem 0.45rem 0.8rem;
        }
    }
</style>
<a id="wa-float" href="<?= htmlspecialchars($wa_link) ?>" target="_blank" rel="noopener" title="Chat on WhatsApp">
    <img id="wa-float-img" src="<?= htmlspecialchars($wa_img) ?>" alt="WhatsApp" />
    <span id="wa-float-text"></span>
</a>
<script>
    // Rotating WhatsApp chat texts
    const waChatTexts = <?= json_encode($wa_chat_texts) ?>;
    let waIndex = 0;
    const waTextElem = document.getElementById('wa-float-text');
    if (waTextElem) {
        waTextElem.textContent = waChatTexts[0];
        setInterval(() => {
            waIndex = (waIndex + 1) % waChatTexts.length;
            waTextElem.textContent = waChatTexts[waIndex];
        }, 2100);
    }
</script>

<!-- Toggle Script (slide-down / fade-in) -->
<script>
    const menuBtn    = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');

    menuBtn?.addEventListener('click', () => {
        const isHidden = mobileMenu.classList.contains('pointer-events-none');

        if (isHidden) {
            // Show: fade in & slide down
            mobileMenu.classList.remove('opacity-0', '-translate-y-2', 'pointer-events-none');
            mobileMenu.classList.add('opacity-100', 'translate-y-0', 'pointer-events-auto');
        } else {
            // Hide: fade out & slide up
            mobileMenu.classList.remove('opacity-100', 'translate-y-0', 'pointer-events-auto');
            mobileMenu.classList.add('opacity-0', '-translate-y-2', 'pointer-events-none');
        }
    });

    // Close if clicking outside the menu or button
    document.addEventListener('click', (e) => {
        if (!menuBtn.contains(e.target) && !mobileMenu.contains(e.target)) {
            mobileMenu.classList.remove('opacity-100', 'translate-y-0', 'pointer-events-auto');
            mobileMenu.classList.add('opacity-0', '-translate-y-2', 'pointer-events-none');
        }
    });
</script>