<x-filament::section>
    <div class="p-2">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">My Referral Link</h2>
        <p class="text-gray-500 dark:text-gray-400 mb-4">
            Share this link with potential clients and agents to earn commission and referral points
        </p>

        <div class="mb-4">
            <div class="flex">
                <input type="text"
                    readonly
                    value="{{ $this->getReferralUrl() }}"
                    class="w-full border border-gray-300 rounded-l-lg px-4 py-2 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                    id="referral-url"
                    onclick="this.select();"
                >
                <button
                    type="button"
                    onclick="copyToClipboard()"
                    class="px-4 py-2 bg-primary-600 text-white rounded-r-lg hover:bg-primary-700 dark:bg-primary-600 dark:hover:bg-primary-700"
                    wire:click="copyReferralLink"
                >
                    Copy
                </button>
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                Your referral code: <span class="font-mono font-bold">{{ auth()->user()->pbo_code }}</span>
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <button
                type="button"
                onclick="shareOnWhatsApp()"
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:ring-4 focus:ring-green-300"
            >
                <span class="mr-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
                    </svg>
                </span>
                WhatsApp
            </button>

            <button
                type="button"
                onclick="shareOnFacebook()"
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:ring-4 focus:ring-blue-300"
            >
                <span class="mr-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"/>
                    </svg>
                </span>
                Facebook
            </button>

            <button
                type="button"
                onclick="shareViaEmail()"
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:ring-4 focus:ring-gray-300"
            >
                <span class="mr-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741ZM1 11.105l4.708-2.897L1 5.383v5.722Z"/>
                    </svg>
                </span>
                Email
            </button>
        </div>
    </div>
</x-filament::section>
@assets
<script>
    function copyToClipboard() {
        const copyText = document.getElementById("referral-url");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);
    }

    function shareOnWhatsApp() {
        const url = document.getElementById("referral-url").value;
        const shareText = "Looking for quality land investments? Use my referral link and get exclusive benefits: " + url;
        window.open('https://wa.me/?text=' + encodeURIComponent(shareText), '_blank');
    }

    function shareOnFacebook() {
        const url = document.getElementById("referral-url").value;
        window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url), '_blank');
    }

    function shareViaEmail() {
        const url = document.getElementById("referral-url").value;
        const subject = "Exclusive Land Investment Opportunity";
        const body = "Hello,\n\nI wanted to share this exclusive land investment opportunity with you. Use my referral link for special benefits:\n\n" + url + "\n\nLet me know if you have any questions!\n\nBest regards,\n" + "{{ auth()->user()->name }}";

        window.location.href = 'mailto:?subject=' + encodeURIComponent(subject) + '&body=' + encodeURIComponent(body);
    }
</script>
@endassets