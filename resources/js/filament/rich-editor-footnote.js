const { Node, mergeAttributes } = window.FilamentRichEditor.tiptap.core
const { Plugin, PluginKey } = window.FilamentRichEditor.tiptap.pmState

export default Node.create({
    name: 'footnote',
    group: 'inline',
    inline: true,
    atom: true,
    selectable: true,

    addAttributes() {
        return {
            id: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-footnote-id'),
                renderHTML: (attributes) => ({
                    'data-footnote-id': attributes.id,
                }),
            },
            number: {
                default: 1,
                parseHTML: (element) => Number(element.getAttribute('data-footnote-number') || 1),
                renderHTML: (attributes) => ({
                    'data-footnote-number': Number(attributes.number || 1),
                }),
            },
            content: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-footnote-content'),
                renderHTML: (attributes) =>
                    attributes.content
                        ? { 'data-footnote-content': attributes.content }
                        : {},
            },
        }
    },

    parseHTML() {
        return [{ tag: 'sup[data-footnote-id]' }]
    },

    renderHTML({ node, HTMLAttributes }) {
        return [
            'sup',
            mergeAttributes({ class: 'edulaw-footnote-ref' }, HTMLAttributes),
            String(node.attrs.number || 1),
        ]
    },

    renderText({ node }) {
        return String(node.attrs.number || 1)
    },

    addCommands() {
        return {
            insertFootnote:
                (attributes) =>
                ({ commands }) =>
                    commands.insertContent([
                        { type: this.name, attrs: attributes },
                        { type: 'text', text: ' ' },
                    ]),
        }
    },

    addProseMirrorPlugins() {
        return [
            new Plugin({
                key: new PluginKey('edulawFootnoteNumbering'),
                appendTransaction: (transactions, oldState, newState) => {
                    if (!transactions.some((transaction) => transaction.docChanged)) {
                        return null
                    }

                    let number = 0
                    let changed = false
                    const transaction = newState.tr

                    newState.doc.descendants((node, position) => {
                        if (node.type.name !== this.name) return

                        number++

                        if (Number(node.attrs.number) === number) return

                        transaction.setNodeMarkup(position, undefined, {
                            ...node.attrs,
                            number,
                        })
                        changed = true
                    })

                    return changed ? transaction : null
                },
            }),
        ]
    },
})
