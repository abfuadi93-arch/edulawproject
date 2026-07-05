@props([
    'title' => null,
    'url' => null,
    'description' => null,
    'label' => 'Bagikan',
])

@php
    $shareTitle = trim(strip_tags((string) ($title ?: config('app.name', 'Edulaw Project'))));
    $shareUrl = trim((string) ($url ?: request()->fullUrl()));
    $shareDescription = trim(strip_tags((string) ($description ?? '')));
    $shareText = trim(collect([$shareTitle, $shareDescription])->filter()->join(' - '));
    $shareMessage = trim(collect([$shareText, $shareUrl])->filter()->join(' '));
    $buttonBase = 'inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-brand-navy shadow-sm transition hover:-translate-y-0.5 hover:border-brand-navy hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber';
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-2']) }} data-edulaw-share-group>
    <span class="mr-1 text-xs font-black uppercase tracking-[0.18em] text-slate-400">
        {{ $label }}
    </span>

    <a
        href="https://wa.me/?text={{ rawurlencode($shareMessage) }}"
        target="_blank"
        rel="noopener"
        class="{{ $buttonBase }}"
        aria-label="Bagikan ke WhatsApp"
    >
        <svg class="h-4 w-4 shrink-0 text-[#25D366]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
            <path d="M12.04 2A9.86 9.86 0 0 0 3.5 16.82L2.25 22l5.31-1.2A9.86 9.86 0 1 0 12.04 2Zm0 1.74a8.12 8.12 0 0 1 6.92 12.38 8.08 8.08 0 0 1-9.72 3.12l-.34-.14-3.22.73.76-3.12-.18-.36A8.12 8.12 0 0 1 12.04 3.74Zm-3.26 3.9c-.18 0-.46.07-.7.34-.24.27-.92.9-.92 2.2s.94 2.56 1.08 2.74c.13.18 1.84 2.94 4.55 4 .56.22 1 .35 1.34.45.56.18 1.08.15 1.48.09.45-.07 1.39-.57 1.59-1.12.2-.55.2-1.02.14-1.12-.06-.1-.22-.16-.46-.28-.24-.12-1.39-.69-1.6-.77-.22-.08-.38-.12-.54.12-.16.24-.62.77-.76.93-.14.16-.28.18-.52.06-.24-.12-1.02-.38-1.94-1.2-.72-.64-1.2-1.43-1.34-1.67-.14-.24-.02-.37.1-.49.11-.11.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.3-.74-1.78-.19-.46-.39-.4-.54-.41h-.46Z" />
        </svg>
        <span class="sr-only">WhatsApp</span>
    </a>

    <a
        href="https://t.me/share/url?url={{ rawurlencode($shareUrl) }}&text={{ rawurlencode($shareText) }}"
        target="_blank"
        rel="noopener"
        class="{{ $buttonBase }}"
        aria-label="Bagikan ke Telegram"
    >
        <svg class="h-4 w-4 shrink-0 text-[#229ED9]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
            <path d="M21.74 4.35c.32-1.22-.78-1.02-1.34-.8L2.95 10.28c-1.2.47-1.18 1.14-.2 1.44l4.48 1.4L17.6 6.58c.49-.3.94-.14.57.19l-8.4 7.58-.32 4.77c.47 0 .68-.22.94-.48l2.26-2.2 4.7 3.47c.87.48 1.49.23 1.7-.8l2.69-14.76Z" />
        </svg>
        <span class="sr-only">Telegram</span>
    </a>

    <a
        href="https://twitter.com/intent/tweet?text={{ rawurlencode($shareText) }}&url={{ rawurlencode($shareUrl) }}"
        target="_blank"
        rel="noopener"
        class="{{ $buttonBase }}"
        aria-label="Bagikan ke X atau Twitter"
    >
        <svg class="h-4 w-4 shrink-0 text-slate-950" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
            <path d="M13.86 10.47 21.2 2h-1.74l-6.37 7.35L8 2H2.12l7.7 11.06L2.12 22h1.74l6.73-7.77L15.97 22h5.88l-7.99-11.53Zm-2.38 2.75-.78-1.1L4.5 3.29h2.67l5.01 7.13.78 1.1 6.5 9.25h-2.67l-5.31-7.55Z" />
        </svg>
        <span class="sr-only">X/Twitter</span>
    </a>

    <a
        href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($shareUrl) }}"
        target="_blank"
        rel="noopener"
        class="{{ $buttonBase }}"
        aria-label="Bagikan ke Facebook"
    >
        <svg class="h-4 w-4 shrink-0 text-[#1877F2]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
            <path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5.02 3.66 9.18 8.44 9.94v-7.03H7.9v-2.9h2.54V9.84c0-2.52 1.49-3.91 3.77-3.91 1.09 0 2.24.2 2.24.2v2.47h-1.26c-1.24 0-1.63.78-1.63 1.57v1.9h2.78l-.44 2.9h-2.34V22C18.34 21.24 22 17.08 22 12.06Z" />
        </svg>
        <span class="sr-only">Facebook</span>
    </a>

    <a
        href="https://www.linkedin.com/sharing/share-offsite/?url={{ rawurlencode($shareUrl) }}"
        target="_blank"
        rel="noopener"
        class="{{ $buttonBase }}"
        aria-label="Bagikan ke LinkedIn"
    >
        <svg class="h-4 w-4 shrink-0 text-[#0A66C2]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
            <path d="M20.45 20.45h-3.56v-5.57c0-1.33-.02-3.04-1.85-3.04-1.86 0-2.14 1.45-2.14 2.95v5.66H9.34V9h3.42v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.61 0 4.27 2.37 4.27 5.46v6.28ZM5.32 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12Zm1.78 13.02H3.54V9H7.1v11.45ZM22.22 0H1.77C.8 0 0 .78 0 1.75v20.5C0 23.22.8 24 1.77 24h20.45c.98 0 1.78-.78 1.78-1.75V1.75C24 .78 23.2 0 22.22 0Z" />
        </svg>
        <span class="sr-only">LinkedIn</span>
    </a>

    <a
        href="mailto:?subject={{ rawurlencode($shareTitle) }}&body={{ rawurlencode(trim(collect([$shareText, $shareUrl])->filter()->join("\n\n"))) }}"
        class="{{ $buttonBase }}"
        aria-label="Bagikan melalui email"
    >
        <svg class="h-4 w-4 shrink-0 text-brand-teal" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
            <path d="M4 6h16v12H4V6Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
            <path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="sr-only">Email</span>
    </a>

    <button
        type="button"
        class="{{ $buttonBase }}"
        data-edulaw-copy-link
        data-share-url="{{ $shareUrl }}"
        data-copy-success-label="Link Disalin"
        aria-label="Salin link untuk Instagram"
    >
        <svg class="h-4 w-4 shrink-0 text-[#E4405F]" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
            <rect x="4" y="4" width="16" height="16" rx="5" stroke="currentColor" stroke-width="2" />
            <circle cx="12" cy="12" r="3.5" stroke="currentColor" stroke-width="2" />
            <circle cx="16.8" cy="7.2" r="1" fill="currentColor" />
        </svg>
        <span class="sr-only" data-edulaw-share-copy-label>Instagram</span>
    </button>

    <button
        type="button"
        class="{{ $buttonBase }}"
        data-edulaw-copy-link
        data-share-url="{{ $shareUrl }}"
        data-copy-success-label="Link Disalin"
        aria-label="Salin link"
    >
        <svg class="h-4 w-4 shrink-0 text-brand-navy" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
            <path d="M10 13a5 5 0 0 0 7.07 0l2.12-2.12a5 5 0 0 0-7.07-7.07L11 4.93" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M14 11a5 5 0 0 0-7.07 0L4.81 13.12a5 5 0 0 0 7.07 7.07L13 19.07" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="sr-only" data-edulaw-share-copy-label>Salin Link</span>
    </button>

    <span class="sr-only" aria-live="polite" data-edulaw-share-status></span>
</div>

@once
    @push('scripts')
        <script>
            (() => {
                if (window.__edulawShareButtonsReady) {
                    return;
                }

                window.__edulawShareButtonsReady = true;

                const copyText = async (text) => {
                    if (navigator.clipboard?.writeText && window.isSecureContext) {
                        await navigator.clipboard.writeText(text);
                        return;
                    }

                    const textarea = document.createElement('textarea');
                    textarea.value = text;
                    textarea.setAttribute('readonly', '');
                    textarea.style.position = 'fixed';
                    textarea.style.top = '-9999px';
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    textarea.remove();
                };

                const announce = (button, message) => {
                    const group = button.closest('[data-edulaw-share-group]');
                    const status = group?.querySelector('[data-edulaw-share-status]');

                    if (status) {
                        status.textContent = message;
                    }
                };

                const markCopied = (button) => {
                    const label = button.querySelector('[data-edulaw-share-copy-label]');
                    const original = label?.textContent;
                    const success = button.dataset.copySuccessLabel || 'Tersalin';

                    if (label) {
                        label.textContent = success;
                    }

                    announce(button, 'Link berhasil disalin');

                    window.setTimeout(() => {
                        if (label && original) {
                            label.textContent = original;
                        }
                    }, 1800);
                };

                document.addEventListener('click', async (event) => {
                    const copyButton = event.target.closest('[data-edulaw-copy-link]');

                    if (! copyButton) {
                        return;
                    }

                    await copyText(copyButton.dataset.shareUrl || window.location.href);
                    markCopied(copyButton);
                });
            })();
        </script>
    @endpush
@endonce
