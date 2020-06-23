<template>
    <div id="addComponent">

        <div class="card text-white bg-dark">
            <div class="card-header">Custom Search</div>

            <div class="card-body">
                <form @submit="store($event)">
                    <!-- data getter section -->
                    <CustomSearchComponent :export-url="searchUrl" :columns="columns" @search="search" />
                </form>
            </div>
        </div>

    </div>
</template>
<script>
import CustomSearchComponent from './CustomSearchComponent';
export default {
    name : 'add-component' ,
    props : {
        'columns' : {
            required : true,
        },
        'searchUrl' : {
            type : String,
        }

    },
    components : {
        'CustomSearchComponent' : CustomSearchComponent,
    },
    data(){
        return {
        }
    },
    created(){
        this.init();
    },
    methods : {
        init(){
        },
        search(data){
            this.$emit('search' , data);
        },
    }
}
</script>

<style lang="scss" scoped>
#addComponent{
    z-index : 1000;
    max-height : 80vh;
    overflow : auto;
}
</style>