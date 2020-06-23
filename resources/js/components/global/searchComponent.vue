<template>
    <div id="search">
        <form :action="search.searchUrl" method="get" @submit.prevent="searchTable()">
            <div class="row">
                <div class="col">
                    <date-picker
                        v-model="date"
                        format="YYYY-MM-DD"
                        type="date"
                        valueType="format"
                        placeholder="Select date"
                    ></date-picker>
                </div>

                <div class="customSearch col" v-if="useCustomSearch || columns">
                    <button class="btn btn-success btn-block" @click="toggleCustomSearch">Custom Search <i class="fa fa-search fa-fw"></i></button>
                </div>

                <div class="col">
                    <input type="text" v-model="searchPhrase" name="search" :placeholder="placeholder" class="form-control">
                </div>
                <div class="col">
                    <button class="btn btn-success btn-block">Search <i class="fa fa-search fa-fw"></i></button>
                </div>
            </div>
        </form>

        <!-- show Component -->
        <transition name="addform">
            <div id="addForm" v-if="showCustomSearchComponent">
                <div class="row">
                    <div class="col-md-3" @click="toggleCustomSearch"></div>
                    <div class="col-md-6">
                        <CustomSearch :searchUrl="search.searchUrl" :columns="columns" @search="searchFunc" />
                    </div>
                    <div class="col-md-3" @click="toggleCustomSearch"></div>
                </div>

                <div class="overlay" @click="toggleCustomSearch"></div>
            </div>
        </transition>

    </div>
</template>

<script>
import DatePicker from 'vue2-datepicker';
import 'vue2-datepicker/index.css';
import CustomSearch from './CustomSearchContainerComponent.vue';

export default {
    name : "searchComponent" ,
    props : {
        "search" : {
            require:  true,
            type : Object,
        },
        'placeholder' : {
            required : true,
        },
        "useCustomSearch" : {
            default : false,
            type : Boolean,
        },
        "columns" : {
            type : Array,
        }
    },
    components : {
        'DatePicker' : DatePicker,
        'CustomSearch' : CustomSearch,
    },
    data(){
        return {
            searchPhrase : '',
            date : '',
            showCustomSearchComponent : false,
        }
    },
    created(){
        this.searchPhrase = this.search.searchPhrase ? this.search.searchPhrase : '';
    },

    methods : {
        searchTable(){
            if(this.date != undefined && this.date.length > 0){
                this.searchPhrase = this.date+'||filter_date'
            }
            this.$emit('searchTableData' , { url : this.search.searchUrl , search : this.searchPhrase , customSearch : this.customSearchData});
            this.customSearchData = null;
        },
        searchFunc(searchData){
            this.customSearchData = searchData;
            this.searchTable();
        },
        toggleCustomSearch(){
            this.showCustomSearchComponent = !this.showCustomSearchComponent;
        }
    }, 
    watch : {
        date(){
            this.$emit('date' , this.date);
        }
    }
}
</script>

<style lang="scss" scoped>
#search{
    .overlay{
        position: fixed;
        top : 0px;
        left : 0px;
        z-index : -1;
        width : 100%;
        height : 100%;
        background-color : rgba(0, 0, 0, 0.418);
    }
    #addForm{
        position: fixed;
        top : 0px;
        left : 0px;
        width : 100%;
        height : 100%;
        z-index : 100000;
        display : flex;
        transition: all 0.5s;
        align-items: center;
        .row{
            flex : 1;
        }
    }
    .addform-enter, .addform-leave-to
    /* .list-complete-leave-active below version 2.1.8 */ {
        opacity: 0;
        transform: translateY(30px);
    }
    .addform-leave-active{
        position: absolute;
    }
}
</style>