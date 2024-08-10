<div
    class="flex-grow flex flex-col max-h-full overflow-hidden"
    :class="$store.editor.minimalMode ? 'h-screen' : 'h-[100vh-40px]'"
    wire:poll.5s
>
    <div
        x-data="codemirroreditor"
        class="flex flex-col flex-grow max-h-full"
    >
        @include('livewire.tinker-editor-parts.settings')
        @include('livewire.tinker-editor-parts.editors')
    </div>

    <script>
        window.editor = null;
        window.outputEditor = null;

        document.addEventListener('alpine:init', () => {
            Alpine.data('codemirroreditor', () => ({
                loading: false,
                // codemirror instances
                editorType: 'monaco', // 'codemirror' or 'monaco'
                // shell state
                code: @entangle('code'),
                output: @entangle('output'),
                path: @entangle('path'),
                title: @entangle('title'),
                php_binary: @entangle('php_binary'),
                isExecuting: false,
                isDockerContext: @entangle('isDockerContext'),
                dockerContainer: @entangle('dockerContainer'),
                dockerWorkdir: @entangle('dockerWorkdir'),
                // view structure
                formOpened: false, // TODO: persist
                displayHorizontal: true, // TODO: persist

                init() {
                    if (this.editorType === 'codemirror') {
                        window.editor = this.startEditor(this.$refs.editor, this.code, false);
                    } else { // monaco
                        window.editor = this.startMonacoEditor(this.$refs.editor, this.code, false);
                    }

                    if (this.editorType === 'codemirror') {
                        window.outputEditor = this.startEditor(this.$refs.output, this.output, true);
                    } else { // monaco
                        window.outputEditor = this.startMonacoEditor(this.$refs.output, this.output, true);
                    }

                    this.$watch('formOpened', () => {
                        window.editor.layout();
                        window.outputEditor.layout();
                    })

                    this.macosFix();
                },

                macosFix() {
                    let that = this;
                    this.$refs.php_binary.addEventListener('keydown', async function (event) {
                        if ((event.ctrlKey || event.metaKey) && event.key === 'v') {
                            that.php_binary = await navigator.clipboard.readText();
                        }
                    });
                    this.$refs.path.addEventListener('keydown', async function (event) {
                        if ((event.ctrlKey || event.metaKey) && event.key === 'v') {
                            that.path = await navigator.clipboard.readText();
                        }
                    });
                },

                // --------------------------------------------
                // CodeMirror : BEGIN
                // --------------------------------------------

                startEditor(domElement, content, readOnly) {
                    return new window.EditorView({
                        state: this.startEditorState(content, readOnly),
                        parent: domElement,
                    });
                },

                startEditorState(content, readOnly) {
                    let language = new window.editorCompartment, tabSize = new window.editorCompartment
                    return window.EditorState.create({
                        doc: content,
                        extensions: [
                            window.editorBasicSetup,
                            language.of(window.editorPhp({plain: true})),
                            tabSize.of(EditorState.tabSize.of(4)),
                            // window.editorKeymap.of([this.executeCode(this.execute.bind(this))]),
                            window.editorKeymap.of([{ key: "Shift-Ctrl-Enter", run: this.executeCode.bind(this) }]),
                            window.gruvboxDark,
                            window.EditorState.readOnly.of(readOnly),
                            window.EditorView.lineWrapping,
                        ],
                    });
                },

                // --------------------------------------------
                // CodeMirror : END
                // --------------------------------------------

                // --------------------------------------------
                // Monaco : BEGIN
                // --------------------------------------------

                startMonacoEditor(domElement, content, readonly) {
                    let monacoEditor = window.monaco.editor.create(domElement, {
                        value: content,
                        language: 'php',
                        automaticLayout: true,
                        minimap: {
                            enabled: false
                        },
                        theme: 'vs-dark',
                        padding: {
                            top: 1,
                        },
                        lineHeight: 23,
                        fontSize: 18,
                        wordWrap: 'on',
                        autoClosingBrackets: 'always',
                        readOnly: readonly,
                    });
                    if (!readonly) { // input editor
                        monacoEditor.getModel()
                            .onDidChangeContent(() => this.code = monacoEditor.getValue());

                        monacoEditor.addCommand(window.monaco.KeyMod.CtrlCmd | window.monaco.KeyMod.Shift | window.monaco.KeyCode.Enter, this.executeCode.bind(this));
                    }
                    return monacoEditor;
                },

                // --------------------------------------------
                // Monaco : END
                // --------------------------------------------

                executeCode(view) {
                    if (this.isExecuting) return;

                    this.isExecuting = true;

                    if (this.editorType === 'codemirror') {
                        return this.execute((view ?? window.editor).state.doc.toString());
                    } else { // monaco
                        return this.execute(this.code);
                    }
                },

                async execute(content) {
                    let result = await this.$wire.executeCode(content);
                    // for html
                    // this.$refs.output.innerHTML = this.makeOutputPretty(result);
                    // for editors

                    if (this.editorType === 'codemirror') {
                        window.outputEditor.dispatch({
                            changes: {
                                from: 0,
                                to: window.outputEditor.state.doc.length,
                                insert: result,
                            },
                        });
                    } else { // monaco
                        window.outputEditor.setValue(result);
                    }

                    this.isExecuting = false;
                },

                makeOutputPretty(output) {
                    const lines = output.trim().split('\n');

                    let html = '<div class="p-4">';
                    html += '<code class="text-white font-mono whitespace-pre">';

                    lines.forEach(line => {
                        let formattedLine = line;

                        if (line.startsWith('[!]')) {
                            formattedLine = `<span class="text-red-600">${line}</span>`;
                        } else if (line.startsWith('#')) {
                            formattedLine = `<span class="text-green-400">${line}</span>`;
                        } else if (line.includes('=>')) {
                            formattedLine = `<span class="text-blue-400">${line}</span>`;
                        } else if (line.includes('{')) {
                            formattedLine = `<span class="text-yellow-400">${line}</span>`;
                        } else if (line.includes('}')) {
                            formattedLine = `<span class="text-yellow-400">${line}</span>`;
                        }

                        html += `<div class="ml-4">${formattedLine}</div>`;
                    });

                    html += '</code>';
                    html += '</div>';

                    return html;
                },
            }));
        });
    </script>
</div>
