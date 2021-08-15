<template>
    <div>
        <transition name="slide">
            <div class="notification fixed"
                 v-if="showNotifcation"
                 :style="setStyle">
                <div class="delete"
                     @click="close()"></div>
                <div class="content" v-html="notification.content">
                </div>
            </div>
        </transition>

    </div>
</template>

<script>
    export default {
        data () {
            return {
                notification: this.options,
                showNotifcation: this.show,
            }
        },
        props: {
            'options': {
                type: Object,
                default: {}
            },
            'show': {
                type: Boolean,
                default: false
            }
        },
        computed: {
            setStyle () {
                return {
                    color: this.notification.textColor || '#fff',
                    background: this.notification.backgroundColor || '#363b3f'
                }
            },
            setTime () {
                return {
                    transition: `all ${(this.notification.duration / 1000) || 3}s linear`,
                }
            }
        },
        methods: {
            countdown () {

                if (this.notification.autoClose === false) {
                    return;
                }

                if(!this.notification.duration){
                    this.notification.duration = 4000;
                }

                const t = setTimeout(() => {
                    this.close()
                }, this.notification.duration)
            },
            close () {
                this.$emit('close') // should to emit to change parent components status
                this.notification = {}

            }
        },
        watch: {
            notification () {
                this.notification = this.options

            },
            show (val) {
                this.showNotifcation = val
                this.countdown()

            }
        },
        mounted() {
            // if( typeof  this.notification.autoClose == "undefined"){
            //     this.notification.autoClose = true;
            // }


        }
    }
</script>

<style scoped>
    .slide-transition {
        transition: all .3s ease;
        transform: translateZ(0);
    }
    .slide-active-enter,
    .slide-active-leave {
        transform: translate3d(0, -100%, 0);
    }

    .slide-enter-active, .slide-leave-active {
        transition: all .3s ease;
        transform: translateZ(0);
    }
    .slide-enter, .slide-leave-active {
        transform: translate3d(0, -100%, 0);
    }
    .delete {
        -moz-appearance: none;
        -webkit-appearance: none;
        background: rgba(51, 51, 51, .2);
        cursor: pointer;
        display: inline-block;
        height: 24px;
        position: relative;
        vertical-align: top;
        width: 24px;
        float: right;
    }
    .delete:after,
    .delete:before {
        background: #fff;
        content: "";
        display: block;
        height: 2px;
        left: 50%;
        margin-left: -25%;
        margin-top: -1px;
        position: absolute;
        top: 50%;
        width: 50%;
    }
    .delete:before {
        transform: rotate(45deg);
    }
    .delete:after {
        transform: rotate(-45deg);
    }
    .delete:hover {
        background: rgba(51, 51, 51, .5);
    }
    .notification {
        width: 30%;
        line-height: 2;
        z-index: 3;
        position: absolute;
        top: 15px;
        right: 15px;
        border-radius: 2px;
        box-shadow: 0 2px 12px 0 rgb(0 0 0 / 10%);
    }
    .notification .content {
        padding: .75rem 2rem;
    }
    .countdown {
        width: 100%;
        height: 4px;
        background: #000;
        z-index: 3;
        position: fixed;
        top: 15px;
        righ: 15px;
        transform: translateZ(0);
    }
    .count-leave {
        transform: translate3d(-100%, 0, 0);
    }
</style>
