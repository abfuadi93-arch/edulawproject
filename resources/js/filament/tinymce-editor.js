const activeContentElements = 'script, iframe, object, embed, form, input, button, textarea, select, option, meta, link, style'

const loadTinyMce = (scriptUrl) => {
    if (window.tinymce) return Promise.resolve(window.tinymce)
    if (window.edulawTinyMcePromise) return window.edulawTinyMcePromise

    window.edulawTinyMcePromise = new Promise((resolve, reject) => {
        const existingScript = document.querySelector(`script[src="${CSS.escape(scriptUrl)}"]`)
        const script = existingScript ?? document.createElement('script')

        script.addEventListener('load', () => resolve(window.tinymce), { once: true })
        script.addEventListener('error', () => reject(new Error('TinyMCE gagal dimuat.')), { once: true })

        if (!existingScript) {
            script.src = scriptUrl
            script.referrerPolicy = 'same-origin'
            document.head.appendChild(script)
        }
    })

    return window.edulawTinyMcePromise
}

const createUuid = () => {
    if (window.crypto?.randomUUID) return window.crypto.randomUUID()

    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (character) => {
        const random = Math.floor(Math.random() * 16)
        const value = character === 'x' ? random : (random & 0x3) | 0x8

        return value.toString(16)
    })
}

const cleanPastedHtml = (html) => {
    const document = new DOMParser().parseFromString(html, 'text/html')
    const comments = document.createTreeWalker(document.body, NodeFilter.SHOW_COMMENT)
    const commentsToRemove = []

    while (comments.nextNode()) commentsToRemove.push(comments.currentNode)
    commentsToRemove.forEach((comment) => comment.remove())

    document.body.querySelectorAll(activeContentElements).forEach((element) => element.remove())

    document.body.querySelectorAll('*').forEach((element) => {
        Array.from(element.attributes).forEach((attribute) => {
            const name = attribute.name.toLowerCase()
            const value = attribute.value.trim().toLowerCase()

            if (name.startsWith('on') || name.startsWith('xmlns') || name === 'lang') {
                element.removeAttribute(attribute.name)
                return
            }

            if (['href', 'src', 'xlink:href'].includes(name) && /^(?:javascript|vbscript|data):/.test(value)) {
                element.removeAttribute(attribute.name)
            }
        })

        if (element.hasAttribute('style')) {
            const textAlign = element.style.textAlign
            element.removeAttribute('style')

            if (['left', 'center', 'right', 'justify'].includes(textAlign)) {
                element.style.textAlign = textAlign
            }
        }

        if (element.tagName.toLowerCase() === 'sup' && element.hasAttribute('data-footnote-id')) {
            element.className = 'edulaw-footnote-ref'
        } else {
            element.removeAttribute('class')
            element.removeAttribute('id')
        }
    })

    document.body.querySelectorAll('font, span').forEach((element) => {
        if (element.tagName.toLowerCase() === 'span' && element.attributes.length > 0) return

        element.replaceWith(...element.childNodes)
    })

    document.body.querySelectorAll('o\\:p').forEach((element) => element.remove())

    return document.body.innerHTML
}

const registerFootnoteButton = (editor) => {
    const renumber = () => {
        editor.dom.select('sup[data-footnote-id]').forEach((marker, index) => {
            const number = index + 1
            editor.dom.setAttrib(marker, 'data-footnote-number', String(number))
            marker.textContent = String(number)
        })
    }

    editor.ui.registry.addButton('footnote', {
        text: 'Footnote',
        tooltip: 'Tambahkan catatan kaki',
        onAction: () => {
            editor.windowManager.open({
                title: 'Tambahkan Catatan Kaki',
                body: {
                    type: 'panel',
                    items: [
                        {
                            type: 'textarea',
                            name: 'content',
                            label: 'Isi catatan kaki',
                        },
                    ],
                },
                buttons: [
                    { type: 'cancel', text: 'Batal' },
                    { type: 'submit', text: 'Tambahkan', buttonType: 'primary' },
                ],
                initialData: { content: '' },
                onSubmit: (dialog) => {
                    const content = dialog.getData().content.trim()

                    if (!content) return

                    const number = editor.dom.select('sup[data-footnote-id]').length + 1
                    const encodedContent = editor.dom.encode(content)

                    editor.insertContent(
                        `<sup class="edulaw-footnote-ref" data-footnote-id="${createUuid()}" data-footnote-number="${number}" data-footnote-content="${encodedContent}">${number}</sup>&nbsp;`,
                    )
                    renumber()
                    dialog.close()
                },
            })
        },
    })

    editor.on('SetContent', renumber)
}

export default function tinyMceEditorFormComponent({
    config,
    height,
    label,
    skinBaseUrl,
    state,
    tinyMceBaseUrl,
    tinyMceScriptUrl,
    uploadFileAttachmentUsing,
}) {
    return {
        editor: null,
        state,
        themeObserver: null,
        currentDarkMode: null,
        isDestroyed: false,

        async init() {
            this.isDestroyed = false

            await loadTinyMce(tinyMceScriptUrl)

            // Let Filament finish its initial relationship-field updates before
            // TinyMCE replaces the textarea and starts managing its own DOM.
            await new Promise((resolve) => setTimeout(resolve, 350))

            if (this.isDestroyed || !this.$root.isConnected) return

            await this.initializeEditor()

            this.themeObserver = new MutationObserver(() => {
                if (this.isDestroyed || this.isDarkMode() === this.currentDarkMode) return

                this.applyTheme()
            })
            this.themeObserver.observe(document.documentElement, {
                attributeFilter: ['class'],
                attributes: true,
            })

            this.$watch('state', () => {
                if (!this.editor || this.editor.hasFocus()) return

                const nextContent = this.state ?? ''
                if (this.editor.getContent() !== nextContent) this.editor.setContent(nextContent)
            })

        },

        async initializeEditor() {
            if (this.isDestroyed || !this.$root.isConnected) return

            const tinymce = window.tinymce

            if (this.editor) {
                this.editor.remove()
                this.editor = null
            }

            const existingEditor = tinymce.get(this.$refs.editor.id)
            if (existingEditor) existingEditor.remove()

            if (!this.$refs.editor.id) {
                this.$refs.editor.id = `edulaw-tinymce-${crypto.getRandomValues(new Uint32Array(1))[0]}`
            }

            this.currentDarkMode = this.isDarkMode()
            const contentSkin = this.currentDarkMode ? 'dark' : 'default'
            const uiSkin = this.currentDarkMode ? 'oxide-dark' : 'oxide'
            const initialContent = this.state ?? ''
            const modal = this.$root.closest('.fi-modal')
            const syncState = Alpine.debounce(() => this.syncState(), 200)

            this.$refs.editor.value = initialContent

            let editors

            try {
                editors = await tinymce.init({
                    ...config,
                    target: this.$refs.editor,
                    base_url: tinyMceBaseUrl,
                    suffix: '.min',
                    height,
                    license_key: 'gpl',
                    promotion: false,
                    branding: false,
                    menubar: false,
                    resize: true,
                    skin_url: `${skinBaseUrl}/ui/${uiSkin}`,
                    content_css: `${skinBaseUrl}/content/${contentSkin}/content.css`,
                    plugins: 'advlist autolink charmap code fullscreen image link lists searchreplace table visualchars wordcount',
                    automatic_uploads: true,
                    images_upload_handler: (blobInfo, progress) =>
                        uploadFileAttachmentUsing(blobInfo.blob(), progress),
                    setup: (editor) => {
                        registerFootnoteButton(editor)

                        editor.on('PastePreProcess', (event) => {
                            event.content = cleanPastedHtml(event.content)
                        })
                        editor.on('input change undo redo PastePostProcess', syncState)
                        editor.on('blur', () => this.syncState())
                        editor.on('OpenWindow', () => modal?.setAttribute('x-trap.noscroll', 'false'))
                        editor.on('CloseWindow', () => modal?.setAttribute('x-trap.noscroll', 'isOpen'))
                    },
                })
            } catch (error) {
                console.error('TinyMCE gagal diinisialisasi.', error)
                throw error
            }

            if (this.isDestroyed || !this.$root.isConnected) {
                editors.forEach((editor) => editor.remove())

                return
            }

            this.editor = editors[0] ?? null
            this.editor?.getContainer()?.setAttribute('aria-label', label || 'Isi artikel')
        },

        isDarkMode() {
            return document.documentElement.classList.contains('dark')
        },

        applyTheme() {
            if (!this.editor) return

            this.currentDarkMode = this.isDarkMode()
            const contentSkin = this.currentDarkMode ? 'dark' : 'default'
            const uiSkin = this.currentDarkMode ? 'oxide-dark' : 'oxide'

            document.querySelectorAll(`link[href^="${skinBaseUrl}/ui/"]`).forEach((stylesheet) => {
                if (stylesheet.href.endsWith('/skin.min.css')) {
                    stylesheet.href = `${skinBaseUrl}/ui/${uiSkin}/skin.min.css`
                }
            })

            this.editor.getDoc().querySelectorAll('link[rel="stylesheet"]').forEach((stylesheet) => {
                if (stylesheet.href.includes(`${skinBaseUrl}/ui/`)) {
                    stylesheet.href = `${skinBaseUrl}/ui/${uiSkin}/content.min.css`
                } else if (stylesheet.href.includes(`${skinBaseUrl}/content/`)) {
                    stylesheet.href = `${skinBaseUrl}/content/${contentSkin}/content.css`
                }
            })
        },

        syncState() {
            if (!this.editor) return

            const content = this.editor.getContent()
            if (content !== this.state) this.state = content
        },

        destroy() {
            this.isDestroyed = true
            this.syncState()
            this.themeObserver?.disconnect()
            this.themeObserver = null

            if (this.editor) {
                this.editor.remove()
                this.editor = null
            }
        },
    }
}
