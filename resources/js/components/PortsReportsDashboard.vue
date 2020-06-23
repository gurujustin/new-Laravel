<template>
    <div id="LinksDashboard">
        <!-- add form -->
        <transition name="addform">
            <div id="addForm" v-if="showReportComponent">
                <div class="row">
                    <div class="col-md-1" @click="showReportComponent = false"></div>
                    <div class="col-md-10">
                        <ReportComponent :url="urls.reportUrl" :urls="urls" :report-summary-data="reportSummary" :report-id="reportId" />
                    </div>
                    <div class="col-md-1" @click="showReportComponent = false"></div>
                </div>

                <div class="overlay" @click="showReportComponent = false"></div>
            </div>
        </transition>

        <!-- show add form -->
        <div id="AddSettingsButton">
            <button class="btn btn-success" @click="addForm = !addForm">Toggle Add {{title}}</button>
        
            <a class="btn btn-info" :href="urls.exportUrl" target="_blank">
                Export {{title}}
            </a>

            <span id="importSection">
                <form :action="urls.importUrl" method="post" ref="import" enctype="multipart/form-data">
                    <input type="file" name="file" @change="$refs.import.submit()">
                    <input type="hidden" name="_token" :value="csrf">
                    <button class="btn btn-warning">
                        Import {{title}}
                    </button>
                </form>
            </span>

            <button class="btn btn-success" @click="showReportComponent = true">Show Port Report</button>
        </div>

        <!-- add form -->
        <transition name="addform">
            <div id="addForm" v-if="addForm">
                <div class="row">
                    <div class="col-md-3" @click="hideAddComponent"></div>
                    <div class="col-md-6">
                        <add-component :show-details="showDetails" :form-details="formDetails" @storeData="store"></add-component>
                    </div>
                    <div class="col-md-3" @click="hideAddComponent"></div>
                </div>

                <div class="overlay" @click="hideAddComponent"></div>
            </div>
        </transition>

        <!-- edit form -->
        <transition name="addform">
            <div id="addForm" v-if="editForm">
                <div class="row">
                    <div class="col-md-3" @click="hideEditComponent"></div>
                    <div class="col-md-6">
                        <EditComponent :show-details="showDetails" :form-details="formDetails" @updateItem="update" :user="editUser"></EditComponent>
                    </div>
                    <div class="col-md-3" @click="hideEditComponent"></div>
                </div>

                <div class="overlay" @click="hideEditComponent"></div>
            </div>
        </transition>
        
        <!-- show Component -->
        <transition name="addform">
            <div id="addForm" v-if="showComponent">
                <div class="row">
                    <div class="col-md-3" @click="hideShowComponent"></div>
                    <div class="col-md-6">
                        <ShowComponent :show-details="showDetails" :columns="columns" :item="showUser"></ShowComponent>
                    </div>
                    <div class="col-md-3" @click="hideShowComponent"></div>
                </div>

                <div class="overlay" @click="hideShowComponent"></div>
            </div>
        </transition>

        <!-- search -->
        <div id="searchSection">
            <searchComponent :columns="columns" :search="localData.search" @searchTableData="searchTable" :placeholder="'Search '+title" />
        </div>

        <!-- show data -->
        <div id="tableSection">
            <IndexComponent @orderIndex="orderIndex" @useItem="useItem" :columns="indexColumns" :actions="actions" :users="localData.gw" @showData="show" @DeleteItem="DeleteItem" @editUser="getEditForm" />
        </div>

        <!-- pagination -->
        <div id="pagination">
            <paginationComponent :pagination="localData.pagination" @changePage="PaginationMove"></paginationComponent>
        </div>

        <!-- alerts -->
        <div id="alertsContainer">
            <alertsComponent :alerts="alerts"></alertsComponent>
        </div>
    </div>
</template>

<script>
import AddComponent from './global/AddComponent';
import ShowComponent from './global/ShowComponent';
import EditComponent from './global/EditComponent';
import alertsComponent from './global/alertsComponent';
import searchComponent from './global/searchComponent';
import IndexComponent from './portsReports/IndexComponent';
import paginationComponent from './global/paginationComponent';
import ReportComponent from './portsReports/ReportComponent';
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
            indexColumns : [
                {
                    name : 'ID' ,
                    column : 'id',
                    order : '',
                    if : false,
                },
                {
                    name : 'Title' ,
                    column : 'title',
                    order : '',
                    if : false,
                },
                {
                    name : 'Dates' ,
                    column : 'dates',
                    order : '',
                    if : false,
                },
                {
                    name : 'Report Type' ,
                    column : 'reportType',
                    order : '',
                    if : false,
                },
                {
                    name : 'Last Update',
                    column : 'updated_at' , 
                    order : '',
                    if : false,
                }
            ],
            actions : [
                'DeleteItem' , 'EditItem' , 'CloneItem', 'useItem'
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