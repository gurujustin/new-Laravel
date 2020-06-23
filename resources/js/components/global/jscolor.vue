<template>
    <div class="input-control color-input">
        <input :value="value"
               :id="id"
               class="jscolor-input {hash:true,styleElement:'',onFineChange:'jsColorOnFineChange(this)'}"
               @change="onChange($event.target)"
               @input="onChange($event.target)"
               @focus="showColorPicker"
               @onFineChange="onFineChange"
               ref="color_input"
               maxlength="7"
        />
        <span class="color-value" ref="color_span" @click="showColorPicker"></span>
    </div>

</template>

<script>
    import jscolor from './../../plugins/jscolor.js';
    export default {
        name: 'jscolor',
        data(){
            return {
                color: ''
            }
        },
        props: [
            'value',
            'id'
        ],
        methods: {
            onChange(target){
                this.color = target.jscolor.toHEXString();
                this.$refs.color_span.style.backgroundColor = this.color;
                this.$emit('input', this.color);
            },
            onFineChange(e){
                this.color = '#' + e.detail.jscolor;
                this.$refs.color_span.style.backgroundColor = this.color;
                this.$emit('input', this.color);
            },
            showColorPicker(){
                this.$refs.color_input.jscolor.show();
            }
        },
        mounted: function () {
            window.jscolor.installByClassName('jscolor-input');
        },
        updated: function () {
            this.$refs.color_span.style.backgroundColor = this.value;
        }
    }
    window.jsColorOnFineChange = function(thisObj){
        thisObj.valueElement.dispatchEvent(new CustomEvent("onFineChange", {detail: {jscolor: thisObj}}));
    }
</script>