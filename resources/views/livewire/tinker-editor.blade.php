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
                dockerType: '{{ $dockerType }}',

                // generic settings
                dockerWorkdir: '{{ $dockerWorkdir }}',
                settingsOpen: {{ $settingsOpen ? 'true' : 'false' }},
                wordWrap: {{ $wordWrap ? 'true' : 'false' }},
                isShowingHidden: {{ $isShowingHidden ? 'true' : 'false' }},
                readOnlyRange: null,

                // remote settings
                isRemoteContext: {{ $isRemoteContext ? 'true' : 'false' }},
                remoteHost: '{{ $remoteHost }}',
                remotePort: '{{ $remotePort }}',
                remoteUser: '{{ $remoteUser }}',
                remotePassword: '{{ $remotePassword }}',
                remotePasswordType: '{{ $remotePasswordType }}',

                //editor settings
                textSize: 'md',

                init() {
                    this.code = this.$wire.code;
                    this.output = this.$wire.output;

                    window.editor = this.startAceEditor(this.$refs.editor, this.code, false);
                    window.outputEditor = this.startAceEditor(this.$refs.output, this.output, true);

                    setTimeout(() => {
                        window.editor.focus();
                        window.editor.getSession().selection.moveCursorTo(1, 0);
                    }, 500);

                    this.macosFix();

                    this.textSize = this.$wire.textSize;
                    this.$watch('textSize', (newValue, oldValue) => {
                        console.log(`textSize changed from ${oldValue} to ${newValue}`);
                    });
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

                startAceEditor(domElement, content, readOnly) {
                    window.ace.config.setModuleUrl("ace/mode/php_worker", "/worker-php.js");

                    const Range = ace.require('ace/range').Range;
                    this.readOnlyRange = new Range(0, 0, 1, 0);

                    const editor = ace.edit(domElement);

                    editor.setOptions({
                        useWorker: !readOnly,
                        showGutter: true,
                        enableSnippets: true,
                        showFoldWidgets: true,
                        foldStyle: "markbeginend",
                        readOnly: readOnly,
                    });

                    editor.setTheme("ace/theme/monokai");
                    this.setCurrentEditorValue(editor, readOnly ? this.output : this.code, readOnly);

                    if (!readOnly) {
                        editor.getSession().setMode({path: "ace/mode/php", inline: true});
                    } else {
                        editor.getSession().setMode({path: "ace/mode/php"});
                    }

                    editor.getSession().setUseWrapMode(this.wordWrap);
                    editor.getSession().on('change', this.saveAceCode.bind(this));

                    editor.commands.removeCommand('addLineBefore'); // command from sublime keymap
                    editor.commands.addCommands([
                        {
                            name: 'executeCode',
                            bindKey: {win: 'Ctrl-Shift-Enter', mac: 'Command-Shift-Enter'},
                            exec: this.executeCode.bind(this),
                            readOnly: false,
                        },
                        {
                            name: 'openSearchBox',
                            bindKey: {win: 'Ctrl-F', mac: 'Command-F'},
                            exec: function (editor) {
                                editor.execCommand('find');
                            },
                            readOnly: false,
                        },
                    ]);

                    return editor;
                },

                getCurrentEditorValue() {
                    return window.editor.getValue();
                },

                setCurrentEditorValue(editor, code, output) {
                    if (!output) {
                        editor.getSession().setValue(code);
                        return;
                    }

                    // this is a quickfix for the output to be able to be collapsed
                    editor.getSession().setValue("<\?php\n\n" + code);
                    editor.getSession().addFold("", this.readOnlyRange);
                    setTimeout(() => {
                        editor.getSession().bgTokenizer.start(0);
                    }, 50);
                },

                saveAceCode(delta) {
                    this.code = this.getCurrentEditorValue();
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
                    let result = await this.execute(this.getCurrentEditorValue());
                    this.setCurrentEditorValue(window.outputEditor, result, true);
                },

                executeCodeRemote() {
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
                                            this.setCurrentEditorValue(window.outputEditor, result);
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
