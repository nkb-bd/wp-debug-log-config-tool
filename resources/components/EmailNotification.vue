<template>
    <div>
        <div class="wpdebugapp_header ">
            <h1 class="wpdebugapp_title">
                Email Notification
            </h1>
            <div class="wpdebugapp_actions">

                <button class="trash button action button-primary" disabled>Save</button>
            </div>
        </div>
        <div class="wpdebugapp_body">

            <table class="table-list">
                <tbody>


                <tr>
                    <td class="header" colspan="2">Send Email when a new debug log is inserted (in next version maybe)</td>
                </tr>
                <tr>
                    <td>Enabled</td>
                    <td>
                        <label class="switch">
                            <input disabled type="checkbox">
                            <span class="slider round"></span>
                        </label>
                    </td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td><input type="text" disabled class="regular-text"></td>
                </tr>

                </tbody>

            </table>


        </div>

    </div>
</template>

<script>
    export default {
        name: "EmailNotification"
    }
</script>

<style scoped>

</style>
