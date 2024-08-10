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
                code: '',
                output: '',
                path: '{{ $path }}',
                title: '{{ $title }}',
                php_binary: '{{ $php_binary }}',
                isExecuting: false,
                isDockerContext: '{{ $isDockerContext }}',
                dockerContainer: '{{ $dockerContainer }}',
                dockerWorkdir: '{{ $dockerWorkdir }}',
                // view structure
                settingsOpen: {{ $settingsOpen ? 'true' : 'false' }},
                displayHorizontal: true, // TODO: persist
                wordWrap: {{ $wordWrap ? 'true' : 'false' }},

                init() {
                    this.code = this.$wire.code;
                    this.output = this.$wire.output;

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
                            enabled: true,
                        },
                        theme: 'vs-dark',
                        padding: {
                            top: 1,
                        },
                        lineHeight: 23,
                        fontSize: 18,
                        wordWrap: this.wordWrap,
                        autoClosingBrackets: 'always',
                        readOnly: readonly,
                        scrollBeyondLastLine: false,
                    });
                    if (!readonly) { // input editor
                        monacoEditor.getModel().onDidChangeContent(this.saveCode.bind(this, monacoEditor));

                        monacoEditor.addCommand(window.monaco.KeyMod.CtrlCmd | window.monaco.KeyMod.Shift | window.monaco.KeyCode.Enter, this.executeCode.bind(this));
                    }
                    return monacoEditor;
                },

                // --------------------------------------------
                // Monaco : END
                // --------------------------------------------

                saveCode(monacoEditor) {
                    this.code = monacoEditor.getValue();
                    this.$wire.saveCode(this.code);
                },

                toggleSettingsForm() {
                    console.log(this.settingsOpen)
                    this.$wire.set('settingsOpen', !this.settingsOpen);
                    this.settingsOpen = !this.settingsOpen;
                    window.editor.layout();
                    window.outputEditor.layout();
                },

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
            }));
        });
    </script>
</div>
