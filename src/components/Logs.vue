<template>

    <div>
        <div class="wpdebugapp_header ">
            <h1 class="wpdebugapp_title">
                Log List
            </h1>
            <div class="wpdebugapp_actions">
                <span class="button " v-if="getFileSize">  Log File Size {{getFileSize()}} </span>
                <button v-if="data.length > 0 " class="trash button action button-primary" @click="clearlog">Clear Log</button>
            </div>
        </div>
        <div class="wpdebugapp_body">

            <table v-if="data.length > 0 ">
                <thead>
                <tr>
                    <th width="15%" scope="col">Type</th>
                    <th width="70%" scope="col">Error</th>
                    <th width="10%" scope="col">Date</th>
                    <th width="5%" scope="col">Action</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="error  in formattedList" :style="getStyle(error)">
                    <td width="5%" data-label="Type" class="header">{{error.type}}</td>
                    <td width="70%" data-label="Error">
                        {{error.details}}
                        <div v-if="error.line != 0" class="details">
                            Line : {{error.line}}
                        </div>
                        <div v-if="error.filePath" class="details">
                            {{error.filePath}}
                        </div>
                    </td>
                    <td width="15%" data-label="Date">{{error.date}}</td>
                    <td width="15%" data-label="Date">{{error.source}}</td>
                </tr>

                </tbody>
            </table>
            <div v-else class="clearfix">

                <p>{{message}}</p>

            </div>
        </div>
    </div>

</template>
<script type="text/babel">

    export default {
        name: 'App',
        data() {
            return {
                activeTab: 'logs',
                data: '',
                message: '',
                fileSize: '',

            };
        },
        methods: {

            fetch() {

                this.$post('', {
                    route: 'fetch_data',
                }).then(data => {

                    if (data.data.data === 'fail_open') {
                        this.message = "Debug log not could not be opened !"
                        return;
                    }
                    if (data.data.data === 'nofile') {
                        this.message = "No debug file found"
                        return;
                    }
                    this.data = data.data.data;
                    this.fileSize = data.data.logsize;
                })
                .catch((error) => {
                    console.error('Error:', error);
                });

            },
            runCommand($id, $source) {

            },
            clearlog() {
                this.$post('', {
                    route: 'clearlog',
                }).then(data => {
                    console.log('Success:', data);
                    this.fetch()
                })
                .catch((error) => {
                    console.error('Error:', error);
                });

            },
            getStyle(error) {
                switch (error.type) {
                    case 'PHP Fatal error':
                        return {backgroundColor: '#f7afaf'};
                    case 'PHP Parse error':
                        return {backgroundColor: '#fad7d7'};
                    case 'PHP Warning':
                        return {backgroundColor: '#faebd7'};
                }
            },
            getFileSize(size = this.fileSize) {
                var units = ['B', 'KB', 'MB'];
                var i = 0;
                while (size >= 1024) {
                    size /= 1024;
                    ++i;
                }
                return parseFloat(size).toFixed(1) + ' ' + units[i];
            }
        },
        computed: {
            // Process entries and prepare for use
            formattedList() {
                const list = this.data.map((entry) => {
                    // Get line number if present
                    var line = entry.details.replace(/.* on line ([\d]+).*/gi, '$1');
                    entry.line = line && line !== entry.details ? line.trim() : '';

                    // Get PHP error type if present
                    var errorType = entry.details.replace(/^(PHP [\w ]+):.*/gi, '$1');
                    entry.type = errorType && errorType !== entry.details ? errorType.trim() : '';

                    if (entry.type === '') {
                        // Check for custom errors
                        if (entry.details.match(/^#[\w_-]+:/gi) !== null) {
                            errorType = entry.details.replace(/^#([\w_-]+):.*/gi, '$1');
                        } else {
                            // Check for Wordpress Database error type if present
                            errorType = entry.details.replace(/^(Wordpress database error ).*/gi, '$1');
                        }

                        entry.type = errorType && errorType !== entry.details ? errorType.trim() : '';
                    }

                    entry.errorTypeKey = entry.type.replace(/[ ]+/gi, '-').toLowerCase();

                    // Get file path if present
                    var filePath = entry.details.replace(/.*in (\/[\w\/._-]+.php).*/gi, '$1');
                    entry.filePath = filePath && filePath != entry.details ? filePath.trim() : '';

                    // Reformat details
                    if (entry.type) {
                        if (entry.line) {
                            entry.details = entry.details.replace('in ' + entry.filePath, '');
                        }

                        if (entry.filePath) {
                            entry.details = entry.details.replace('on line ' + entry.line, '');
                        }

                        entry.details = entry.details.replace(/^(PHP [\w ]+:|#[\w_-]+:|Wordpress database error)(.*)/gi, '$2', '').trim();
                    }
                });
                return this.data;
            }
        },
        mounted() {
            this.fetch();
        }

    }
</script>

