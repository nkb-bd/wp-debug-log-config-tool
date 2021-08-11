<template>
    <div>
        <div class="wpdebugapp_header ">
            <h1 class="wpdebugapp_title">
                Debug Constants
            </h1>
            <div class="wpdebugapp_actions">
                <button class=" button action button-primary" v-show="!deubgConstantExist" @click="enableDebuging()">Enable All Debuging Constant</button>
                <button class=" button action button-primary" @click="save()">Save</button>
                <button class=" button action button-primary" @click="insertNew()">Add New</button>
            </div>
        </div>
        <div class="wpdebugapp_body">

            <table class="table-list">
                <thead>
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Value</th>
                    <th scope="col" class="actions">Action</th>
                </tr>
                </thead>
                <tbody>


                <tr v-for="(item,index)  in formattedConstList" :key=item.name >
                    <td>{{item.name}} </td>
                    <td>
                        {{item.value}}


                    </td>
                    <td class="actions" v-if="checkIfCoreConstants(item.name)">
                        {{item.value}}
                        <label class="switch">
                            <input type="checkbox" v-model="item.value">
                            <span class="slider round"></span>
                        </label>
                    </td>
                    <td class="actions" v-if="!checkIfCoreConstants(item.name)">
                        <button @click="editOption(index,item)"><i class="dashicons dashicons-edit"></i></button>
                        <button @click="removeOption(index)"><i class="dashicons dashicons-trash"></i></button>
                    </td>

                </tr>

                </tbody>
            </table>

            <div class="wpdd_modal" v-show="showModal">

                <div class="wpdd_modal-content ">
                    <span class="wpdd_modal_close" @click="showModal = false">&times;</span>
                    <table class="table-list">

                        <tbody>
                        <tr>
                            <td class="header">
                                Name
                            </td>
                            <td class="header">
                                <input v-model="constant.name" @keydown.space.prevent class="regular-text" type="text">
                            </td>
                        </tr>
                        <tr>
                            <td class="header">
                                Value
                            </td>
                            <td class="header">

                                <input v-if="constant.type=='normal'" v-model="constant.value" @keydown.space.prevent class="regular-text" type="text">
                                <div v-if="constant.type=='raw'">
                                    <label> True</label>
                                    <input type="radio" v-model="constant.value" v-bind:value="true">
                                    <label> False</label>
                                    <input  type="radio" v-model="constant.value" v-bind:value="false">
                                </div>

                            </td>
                        </tr>
                        <tr v-if="!checkIfCoreConstants(constant.name)">
                            <td class="header">
                                Name
                            </td>
                            <td class="header">
                                <select class="regular-text" v-model="constant.type">
                                    <option value="normal">String</option>
                                    <option value="raw">Boolean</option>
                                </select>

                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td class="header">
                                <button :disabled="!validate()" class="button action button-primary" v-if="!editing" @click="addOption()"> Save</button>
                                <button :disabled="!validate()" class="button action button-primary" v-if="editing" @click="updateOption(constant)">  Update</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>

    </div>

</template>
<script type="text/babel">

    export default {
        name: 'App',
        data() {
            return {
                editing: false,
                editIndex: '',
                activeTab: 'logs',
                constantList: [],
                constant: {
                    name: '',
                    value: '',
                    type: 'normal'
                },
                message: '',
                showModal: false,
                deubgConstantExist: false
            };
        },
        methods: {
            addOption(i) {
                console.log('here')
                console.log(this.constant)
                if (!this.constant.name || !this.constant.value) {
                    this.$emit('notification', {text: 'Empty value can not be inserted !', type: 'success'})

                    return;
                }
                this.constantList.push({
                    name: this.constant.name,
                    value: this.constant.value,
                    type: this.constant.type,
                })
                this.showModal = false;
                this.$emit('notification', {text: 'Success Test', type: 'success'})
            },
            updateOption(item) {
                this.showModal = false;

                console.log('update')

                this.constantList[this.editIndex] = this.constant;
                this.editIndex = null;
                this.constant = {
                    name: '',
                    value: '',
                    type: ''
                };
                this.$emit('notification', {text: 'Success Test', type: 'success'})
                this.editing = false;
                this.formattedConstList;
            },
            editOption(i,item) {
                this.showModal = true;
                this.editing = true;
                console.log(item)
                this.constant = item;
                this.editIndex = i;

            },
            removeOption(i) {
                this.constantList.splice(i, 1);
            },
            checkIfCoreConstants(constantItem) {
                let coreConstants = {
                    WP_DEBUG: 'WP_DEBUG',
                    WP_DEBUG_LOG: 'WP_DEBUG_LOG',
                    SCRIPT_DEBUG: 'SCRIPT_DEBUG',
                };

                return !!coreConstants[constantItem.toUpperCase()];

            },
            checkIfCoreConstantsMissing() {
                this.$get('', {
                    route: 'check_constant',
                }).then(data => {
                    if (data.data.exists == true) {
                        this.deubgConstantExist = true;
                    } else {
                        this.deubgConstantExist = false;
                    }

                })
                    .catch((error) => {
                        console.error('Error:', error);
                    })
                    .always(() => {
                        this.fetch();
                    });
                ;
            },
            fetch() {

                this.$post('', {
                    route: 'get_constant',
                }).then(data => {

                    this.constantList = data.data.data
                    if (!this.constantList) {
                        this.constantList = [];
                    }
                })
                    .catch((error) => {
                        console.error('Error:', error);
                    });

            },
            save() {
                if (this.constantList.length == 0) {
                    this.$emit('notification', {text: 'Empty value can not be inserted !', type: 'success'})

                }
                let data = {
                    data: JSON.stringify(this.constantList),
                    route: 'save_constant'
                }
                this.$post('', data)
                    .then(res => {
                        console.log(res)

                        if (res.success && res.success === true) {
                            this.$emit('notification', {text: 'Constants Updated!', type: 'success'})

                            this.checkIfCoreConstantsMissing();

                        }
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                    })
                    .always((e) => {
                        console.log('her',e)
                        this.fetch();
                    });

            },

            insertNew() {
                this.constant = {
                    name: '',
                    value: '',
                    type: 'normal'
                };
                this.showModal = true;
            },
            enableDebuging() {
                let data = {
                    route: 'enableAllDebuging'
                };
                this.$post('', data)
                    .then(res => {
                        if (res) {
                            this.$emit('notification', {text: 'Debug Constant Updated Successfully', type: 'success'})
                        }
                        this.checkIfCoreConstantsMissing();
                    })
            },

            validate() {
                if(this.constant.type=='raw' && this.constant.name){
                    return true;
                }
                if (this.constant.name && this.constant.value) {
                    return true;
                }
            }
        },
        computed: {
            formattedConstList() {
                return this.constantList.filter(function (item) {
                    if (item.type === 'raw' && typeof item.value != "boolean") {
                        item.value = (item.value.toLowerCase() === 'true')

                    }
                    return item;
                });
            }
        },
        mounted() {
            this.checkIfCoreConstantsMissing();


        }


    }
</script>

