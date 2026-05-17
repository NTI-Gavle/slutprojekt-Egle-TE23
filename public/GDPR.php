<?php
/*fuck gdpr asåååå*/
/*chats gtps fick skriva det här*/
$pageTitle = "Privacy Policy";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="m-5">
    <div class="feed">
        <div class="p-container" style="margin-bottom:20px">
            <div class="p-header">
                <h1 style="margin:0">&ltprivacy policy&gt</h1>
            </div>
            <div class="p-content" style="color:var(--post-text-color);line-height:1.8">

                <p style="opacity:0.6;font-size:0.85em">Last updated: <?= date('Y')?></p>

                <h2 style="font-size:1.1em;margin-top:10px">1. Who we are</h2>
                <p>LO-GO is an awesome social posting platform. When you use this site, we collect certain personal data to provide our service. This policy explains what we collect, how we use it, and your rights under the GDPR (General Data Protection Regulation).</p>

                <h2 style="font-size:1.1em;margin-top:16px">2. What data we collect</h2>
                <p>We collect the following personal data when you register or use LO-GO:</p>
                <ul style="padding-left:20px">
                    <li>Email address — used for account login and password resets.</li>
                    <li> Username and nickname — displayed publicly on your profile and posts.</li>
                    <li> Password — stored as a secure bcrypt hash; we never store plain-text passwords.</li>
                    <li> Profile picture and banner image — uploaded voluntarily and displayed publicly.</li>
                    <li> Posts and comments — content you create on the platform.</li>
                    <li> Direct messages — messages sent between users.</li>
                    <li> Date of birth — optional, stored to personalise your profile.</li>
                    <li> Search history — recent searches stored to improve your search experience.</li>
                    <li> Cookies — we use cookies to remember your preferences (dark mode, background animation). No tracking or advertising cookies are used.</li>
                </ul>

                <h2 style="font-size:1.1em;margin-top:16px">3. Why we collect it</h2>
                <p>We process your data on the basis of contract (to provide the service you signed up for) and  legitimate interest (to keep the platform safe and functional). Cookie preferences are stored based on your consent.</p>

                <h2 style="font-size:1.1em;margin-top:16px">4. How we use your data</h2>
                <ul style="padding-left:20px">
                    <li>To authenticate you and keep your account secure.</li>
                    <li>To display your profile, posts, and comments to other users.</li>
                    <li>To send password-reset emails when you request them.</li>
                    <li>To allow administrators to moderate content and remove harmful material.</li>
                </ul>

                <h2 style="font-size:1.1em;margin-top:16px">5. Who we share your data with</h2>
                <p>We do  not  sell or share your personal data with third parties for marketing.</p>

                <h2 style="font-size:1.1em;margin-top:16px">6. Data retention</h2>
                <p>Your data is kept as long as your account exists. When an account is deleted by an administrator, all associated posts, comments, messages, and profile data are permanently removed.</p>

                <h2 style="font-size:1.1em;margin-top:16px">7. Your rights (GDPR)</h2>
                <p>Under the GDPR you have the right to:</p>
                <ul style="padding-left:20px">
                    <li> Access  — request a copy of the personal data we hold about you.</li>
                    <li> Rectification  — correct inaccurate data via the Settings page.</li>
                    <li> Erasure ("right to be forgotten")  — request deletion of your account and all associated data.</li>
                    <li> Restriction  — ask us to limit how we process your data.</li>
                    <li> Objection  — object to processing based on legitimate interest.</li>
                    <li> Portability  — receive your data in a structured, machine-readable format.</li>
                </ul>
                <p>To exercise any of these rights, please contact us via the <a href="contact.php" class="link-p">contact page</a>.</p>

                <h2 style="font-size:1.1em;margin-top:16px">8. Security</h2>
                <p>We take reasonable technical measures to protect your data, including password hashing (bcrypt), prepared SQL statements to prevent injection attacks, CSRF tokens on sensitive forms, and input sanitisation to prevent XSS attacks.</p>

                <h2 style="font-size:1.1em;margin-top:16px">9. Cookies</h2>
                <p>We store two preference cookies:</p>
                <ul style="padding-left:20px">
                    <li>darkmode— remembers your colour theme preference.</li>
                    <li>animated-bg — remembers whether the starfield animation is enabled.</li>
                </ul>
                <p>These cookies contain no personal data and expire after 1 year. No third-party tracking cookies are used.</p>

                <h2 style="font-size:1.1em;margin-top:16px">10. Contact</h2>
                <p>If you have any questions about this policy or want to exercise your rights, please use the <a href="contact.php" class="link-p">contact form</a>.</p>

            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>