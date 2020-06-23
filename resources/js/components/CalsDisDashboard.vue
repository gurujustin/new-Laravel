<template>
    <div id="LinksDashboard">

        <div class="col-lg-12">
            <ReportComponent :url="urls.reportUrl" :urls="urls" :report-summary-data="reportSummary" :report-id="reportId" />
        </div>
       <!-- show data -->
        <div id="tableSection">
            <IndexComponent @orderIndex="orderIndex" :columns="indexColumns" :actions="actions" :users="localData.gw" @showData="show" @DeleteItem="DeleteItem" @editUser="getEditForm" />
        </div>
    </div>               
</template>

<script>
import AddComponent from './global/AddComponent';
import ShowComponent from './global/ShowComponent';
import EditComponent from './global/EditComponent';
import alertsComponent from './global/alertsComponent';
import searchComponent from './global/searchComponent';
import IndexComponent from './calsdis/IndexComponent';
import paginationComponent from './global/paginationComponent';
import ReportComponent from './calsdis/ReportComponent';
export default {
    name : "ScriptsDashboard" , 
    props : {
        "gw" : {
            type : Array ,
            required : true,
        },
        "search" : {
            type : Object,
            required : true,
        },
        'urls' : {
            type : Object,
            required : true,
        },
        "pagination" : {
            required : true,
            type : Object,
        }
    },
    components : {
        'add-component' : AddComponent,
        'ShowComponent' : ShowComponent,
        'alertsComponent' : alertsComponent,
        'searchComponent' : searchComponent,
        'IndexComponent' : IndexComponent,
        'paginationComponent' : paginationComponent,
        'EditComponent'     : EditComponent,
        'ReportComponent' : ReportComponent,
    },
    data(){
        return { 
            alerts : {
                successMessages : [],
                errorMessages : [],
            },
            ports : [],
            currentSort:'port',
            currentSortDir:'asc',
            showReportComponent : true,
            csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            reportSummary : [],
            addForm : false,
            editForm : false,
            showComponent : false,
            editUser : {},
            showUser : {},
            localData : {},
            columns : [
                {
                    name : 'Title' , 
                    column : 'title',
                },
                {
                    name : 'Compare Hash' , 
                    column : 'compare_hash',
                },
                {
                    name : 'Is Last' , 
                    column : 'isLast',
                },
                {
                    name : 'Title' , 
                    column : 'title',
                },
                {
                    name : 'Summary',
                    column : 'summary'
                },
                {
                    name : 'Dates',
                    column : 'dates'
                },
                {
                    name : 'Report Type',
                    column : 'reportType'
                },
                {
                    name : 'Last Update' , 
                    column : 'updated_at',
                },
                {
                    name : 'Creation Date' , 
                    column : 'created_at',
                },
            ],
           
          
            showDetails : {
                name : 'Report',
                column : 'title'
            },
            formDetails : [
                {
                    name : 'Title' , 
                    placeholder : 'Write Report Title' , 
                    type : 'text' , 
                    required : true,
                    class : 'form-control',
                    column : 'title',
                },
                {
                    name : 'Compare Hash' , 
                    placeholder : 'Write Report Compare Hash' , 
                    type : 'text' , 
                    required : true,
                    class : 'form-control',
                    column : 'compare_hash',
                },
                {
                    name : 'Is Last' , 
                    placeholder : 'Write Report Is Laste' , 
                    type : 'checkbox' , 
                    required : false,
                    class : 'form-control',                   
                    column : 'isLast',
                },
                {
                    name : "Report" ,
                    placeholder : 'Write Report Report' , 
                    type : 'textarea' , 
                    required : true,
                    class : 'form-control',
                    column : "report",
                },
                {
                    name : 'Summary',
                    placeholder : 'Write Report Summary' , 
                    type : 'textarea' , 
                    required : true,
                    class : 'form-control',
                    column : 'summary'
                },
                {
                    name : 'Report Type',
                    placeholder : 'Write Report Report Type' , 
                    type : 'textarea' , 
                    required : false,
                    class : 'form-control',
                    column : 'reportType'
                },
            ],
            title : 'Report',
        }
    },
    created(){
        this.init();
    },
    methods : {
        init(){
            this.localData.gw = this.gw;
            this.localData.pagination = this.pagination;
            this.localData.search = this.search;
            this.localData.urls = this.urls;
        },

        sort:function(s) {
            //if s == current sort, reverse
            if(s === this.currentSort) {
            this.currentSortDir = this.currentSortDir==='asc'?'desc':'asc';
            }
            this.currentSort = s;
        },

        useItem(data){
            this.reportSummary = data.content;
            this.reportId = data.id;
            window.scrollTo(0,0);
            this.showReportComponent = true;
        },
        orderIndex(data){
            this.indexColumns = this.indexColumns.map(item => {
                if(item.column == data.column){
                    if(data.type == 'DESC'){
                        item.order = 'ASC';
                    }else{
                        item.order = 'DESC';
                    }
                }
                return item;
            });

            axios.get(`${this.search.searchUrl}?order=${data.column}&orderBy=${data.type}&api=1`)
            .then(r => {
                let data = r.data;
                this.localData.gw = data.gw;
                this.localData.pagination = data.pagination;
                this.localData.search = data.search;
                this.localData.urls = data.urls;
                this.$forceUpdate();
            }).catch(e => {
                console.log(e);
            });
        },
        store(data){
            axios.post(this.urls.addUrl , data)
            .then(r => {
                this.localData.gw.unshift(r.data);
                this.newAlert('New '+this.title+' Added' , `${this.title} ${r.data.name} Was Added Successfully` , true);
                this.hideAddComponent();
                this.$forceUpdate();
            }).catch(e => {
                let error = e.response.data;
                this.newAlert('Gateway Issue' , error.error , false);
            });
            
        },
        DeleteItem(id){
            let gw = this.localData.gw[id];

            axios.post(gw.deleteUrl).then((r) => {
                this.localData.gw.splice(id , 1);
                this.newAlert("Remove Gateway" , r.data.success , true);
            }).catch(e => {
                // let date = new Date().toDateString;
                // this.newAlert("Remove Gateway Error" ,`Unexpected Error ${e.response.data}` , false);
                console.log(e);
            });
        },
        /**
         * show
         */
        show(index){
            this.showUser = this.localData.gw[index];
            this.showShowComponent();
        },
        /**
         * this function add new alert
         */
        newAlert(title , message , success){
            if(success){
                this.alerts.successMessages.push({title:title , text : message});
            }else{
                this.alerts.errorMessages.push({title:title , text : message});
            }
        },

        /**
         * show add Form Component
         */
        showAddComponent(){
            this.addForm = true;
        },
        /**
         * hide add Form Component
         */
        showEditComponent(){
            this.editForm = true;
        },
        /**
         * show add Form Component
         */
        showShowComponent(){
            this.showComponent = true;
        },
        /**
         * hide add Form Component
         */
        hideAddComponent(){
            this.addForm = false;
        },
        /**
         * hide add Form Component
         */
        hideEditComponent(){
            this.editForm = false;
        },
        /**
         * show add Form Component
         */
        hideShowComponent(){
            this.showComponent = false;
        },


        /**
         * pagination move
         */
        PaginationMove(data){
            axios.get(`${data.url}?offset=${data.offset}&perpage=${data.perpage}&search=${this.localData.search.searchPhrase == false ? '' : this.localData.search.searchPhrase}&api=1`)
            .then(r =>{
                let data = r.data;
                this.localData.gw = data.gw;
                this.localData.pagination = data.pagination;
                this.localData.search = data.search;
                this.localData.urls = data.urls;
                this.$forceUpdate();
            }).catch(e =>{
                console.log(e);
            });
        },


        /**
         * search
         */
        searchTable(data){
            let ss;
            if(data.customSearch != null){
                ss = `${data.customSearch}?perpage=${this.localData.pagination.perpage}&offset=${this.localData.pagination.offset}`;
            }else{
                ss = `${data.url}&perpage=${this.localData.pagination.perpage}&offset=${this.localData.pagination.offset}&search=${data.search}&api=1`;
            }
            axios.get(ss)
            .then(r => {
                let data = r.data;
                this.localData.gw = data.gw;
                this.localData.pagination = data.pagination;
                this.localData.search = data.search;
                this.localData.urls = data.urls;
                this.$forceUpdate();
            }).catch(e => {
                console.log(e);
            });
        },


        /**
         * edit form
         */
        getEditForm(data){
            this.editUser = data;
            this.editForm = true;
        },

        /**
         * update form data
         */
        update(data){
            axios.post(data.updateUrl , data)
            .then((r) => {
                this.localData.gw = this.localData.gw.map(item => {
                    if(item.id == r.data.id){
                        return r.data;
                    }
                    return item;
                });
                this.newAlert("Update "+this.title ,`${r.data.Name} Updated Successfully` , true);
                this.hideEditComponent();
                this.$forceUpdate();
            }).catch(e => {
                let errors = e.response.data;
                if(errors.error){
                    this.newAlert('Update '+this.title+' Error', errors.error , false);
                }
            });
        },
        ports:function() {
            return this.ports.sort((a,b) => {
            let modifier = 1;
            if(this.currentSortDir === 'desc') modifier = -1;
            if(a[this.currentSort] < b[this.currentSort]) return -1 * modifier;
            if(a[this.currentSort] > b[this.currentSort]) return 1 * modifier;
            return 0;
            });
        },

    }
}
</script>

<style lang="scss" scoped>
#LinksDashboard{
    #importSection{
        cursor: pointer;
        form{
            position: relative;
            display : inline-block;
            input{
                position : absolute;
                cursor: pointer;
                z-index : 11111;
                opacity: 0;
                width : 100%;
                height : 100%;
            }
        }
    }
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
            overflow : auto;
            max-height : 100%;
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

    /******************alerts section */
    #alertsContainer{
        position: fixed;
        z-index : 10000000000;
        left : 10px;
        bottom : 10px;
        width : 300px;
    }


    // pagination and add button
    #addButton , #pagination{
        display : inline-block;
    }
    #pagination{
        float : right;
    }
}
</style>