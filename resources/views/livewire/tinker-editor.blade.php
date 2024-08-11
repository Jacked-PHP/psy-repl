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
                displayHorizontal: true, // TODO: persist

                // docker settings
                isDockerContext: '{{ $isDockerContext }}',
                dockerContainer: '{{ $dockerContainer }}',

                // generic settings
                dockerWorkdir: '{{ $dockerWorkdir }}',
                settingsOpen: {{ $settingsOpen ? 'true' : 'false' }},
                wordWrap: {{ $wordWrap ? 'true' : 'false' }},

                // remote settings
                isRemoteContext: {{ $isRemoteContext ? 'true' : 'false' }},
                remoteHost: '{{ $remoteHost }}',
                remotePort: '{{ $remotePort }}',
                remoteUser: '{{ $remoteUser }}',
                remotePassword: '{{ $remotePassword }}',
                remotePasswordType: '{{ $remotePasswordType }}',

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
                    if (!this.isRemoteContext) {
                        this.executeCodeLocal(view);
                    } else {
                        this.executeCodeRemote(view);
                    }
                    this.isExecuting = false;
                },

                async executeCodeLocal(view) {
                    if (this.editorType === 'codemirror') {
                        let result = this.execute((view ?? window.editor).state.doc.toString());
                        window.outputEditor.dispatch({
                            changes: {
                                from: 0,
                                to: window.outputEditor.state.doc.length,
                                insert: result,
                            },
                        });
                    } else if (this.editorType === 'monaco') { // monaco
                        let result = await this.execute(this.code);
                        window.outputEditor.setValue(result);
                    } else {
                        let message = 'Unknown editor type';
                        console.error(message);
                        alert(message);
                    }
                },

                executeCodeRemote() {
                    if (this.editorType === 'codemirror') {
                        // let result = this.execute((view ?? window.editor).state.doc.toString());
                        // window.outputEditor.dispatch({
                        //     changes: {
                        //         from: 0,
                        //         to: window.outputEditor.state.doc.length,
                        //         insert: result,
                        //     },
                        // });
                        const message = 'Remote execution is not supported for CodeMirror';
                        console.error(message);
                        alert(message);
                    } else if (this.editorType === 'monaco') {
                        let result = '';
                        fetch('/execute-remote/{{ $shellId }}').then(response => {
                            const reader = response.body.getReader();
                            const decoder = new TextDecoder("utf-8");
                            function processStream({ done, value }) {
                                if (done) {
                                    console.log("Stream complete");
                                    return;
                                }
                                const chunk = decoder.decode(value, { stream: true });
                                const events = chunk.split("\n\n");
                                events.forEach(event => {
                                    if (event.trim().length > 0) {
                                        const lines = event.trim().split("\n");
                                        lines.forEach(line => {
                                            if (line.startsWith("data:")) {
                                                result += line.replace("data: ", "") + "\n";
                                                window.outputEditor.setValue(result);
                                            }
                                        });
                                    }
                                });
                                return reader.read().then(processStream);
                            }

                            // Start reading the stream
                            return reader.read().then(processStream);
                        }).catch(error => {
                            console.error("Fetch error:", error);
                        });
                    } else {
                        const message = 'Unknown editor type';
                        console.error(message);
                        alert(message);
                    }
                },

                /**
                 * @param content
                 * @returns {Promise}
                 */
                execute(content) {
                    return this.$wire.executeCode(content);
                },
            }));
        });
    </script>
</div>
