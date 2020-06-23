<template>
    <div id="ReportComponent">
        <div class="card text-white bg-dark">
        <div class="card-header">
        <div id="dateFilterSection">
            <div id="date">
                <div class="row">
                    <div class="col-md-3">
                        <DatePicker 
                        v-model="startDate"
                        type="datetime"
                        valueType="format"
                        placeholder="Select Report Start Date" />
                    </div>
                    <div class="col-md-3">
                        <DatePicker 
                        v-model="endDate"
                        type="datetime"
                        valueType="format"
                        placeholder="Select Report End Date" />
                    </div>
                    
                    <!-- <div class="col-md-1">
                        <button class="btn btn-success" @click="store">Store</button>
                    </div> -->
                    <div class="col-md-3">
                        <model-select :options="ports" v-model="selectedPort" placeholder="select Port"></model-select>
                    </div>
                    
                    <div class="col-md-3">
                        <model-select :options="filters" v-model="selectedFilter" placeholder="select Filter Type"></model-select>
                    </div>
                    
                    <div class="col-md-2" style="margin-left: 582px; margin-top: 10px;">
                        <input type="text" v-model="reportTitle" placeholder="Report Title" class="form-control">
                    </div>

                    <div class="col-md-2" style=" margin-top: 10px;">
                        <select class="form-control" v-model="exportType">
                            <option value="PDF">PDF</option>
                            <option value="XSLX">XSLX</option>
                            <option value="CSV">CSV</option>
                        </select>
                    </div>
                
                    <div class="col-md-1" style=" margin-top: 10px;">
                        <button class="btn btn-success">Export</button>
                    </div>
                   
                    

                    <!-- <div class="col-md-2">
                        <label for="enableDates" class="btn btn-success">
                            Enable Dates
                        </label>
                    </div>
                    <div class="col-md-3">
                        <input type="checkbox" class="form-control" id="enableDates" v-model="enableDates">
                    </div> -->
                </div>
            </div>
        </div>
        
        <!-- show Component -->
        <transition name="addform">
            <div id="addForm" v-if="showDetailed">
                <div class="row">
                    <div class="col-md-1" @click="showDetailed = false"></div>
                    <div class="col-md-10">
                        <IndexComponent :users="reportDetailes" :columns="showColumns" :actions="actions"></IndexComponent>
                        <!-- pagination -->
                        <paginationComponent :pagination="pagination" @changePage="PaginationMove"></paginationComponent>
                    </div>
                    <div class="col-md-1" @click="showDetailed = false"></div>
                </div>

                <div class="overlay" @click="showDetailed = false"></div>
            </div>
        </transition>
        </div>

        <div class="card-body">
        <table class="table table-dark table-bordered table-responsive table-hover table-striped">
            <thead>
                <tr>
                    <td @click="sort('port')">Port</td>
                    <td @click="sort('totalCalls')">Total Calls</td>
                    <td @click="sort('answeredCalls')">Total Answered</td>
                    <td @click="sort('failedCalls')">Total Failed</td>
                    <td @click="sort('busyCalls')">Total Busy</td>
                    <td @click="sort('noAnswerCalls')">Total No Answer</td>
                    <td @click="sort('elseCalls')">Total Else Status</td>
                    <td @click="sort('billmin')">Bill Min</td>
                    <td @click="sort('dispositions')">
                        Dispositions
                    </td>
                    <!-- <template v-if="reportSummary[`cdr-1`] != undefined" >
                        <td v-for="(disposition, key) in reportSummary[`cdr-1`].dispositions" v-bind:key="disposition+Math.random()" >
                            {{key}}
                        </td>
                    </template> -->
                    <!-- <td>Total Repeated Calls</td> -->
                    <td @click="sort('uniqueCalls')">Total Unique Calls</td>
                    <td @click="sort('enabledNumbers')">Total Numbers Enabled</td>
                    <td @click="sort('disabledNumbers')">Total Numbers Disabled</td>
                    <td @click="sort('k')">Date</td>
                </tr>
            </thead>
            <template v-if="!enableDates">
                <tr v-for="port in ports" v-bind:key="port.value" id="reportSummary" 
                    v-if="
                        (
                            !loading &&
                            (selectedPort.value == '' || selectedPort.value == undefined) 
                            && 
                            (
                                selectedFilter.value == '' || 
                                selectedFilter.value == 'SHOW_ALL' || 
                                (selectedFilter.value == 'HIDE_EMPTY_PORTS' && reportSummary[`cdr-${port.value}`].totalCalls > 0) ||
                                (selectedFilter.value == 'SHOW_EMPTY_PORTS' && reportSummary[`cdr-${port.value}`].totalCalls == 0)
                            )
                        ) 
                        || 
                        selectedPort.value == port.value
                    ">
                    <td>{{port.value}}</td>
                    <td>{{reportSummary[`cdr-${port.value}`].totalCalls}}</td>
                    <td>{{reportSummary[`cdr-${port.value}`].answeredCalls}}</td>
                    <td>{{reportSummary[`cdr-${port.value}`].failedCalls}}</td>
                    <td>{{reportSummary[`cdr-${port.value}`].busyCalls}}</td>
                    <td>{{reportSummary[`cdr-${port.value}`].noAnswerCalls}}</td>
                    <td>{{reportSummary[`cdr-${port.value}`].elseCalls}}</td>
                    <td>{{reportSummary[`cdr-${port.value}`].billmin}}</td>
                    <td>
                        Dispositions
                        <ul class="dispositions" v-if="showDipositions[`port-${port.value}`]"> 
                            <li class="close"><span><i class="fa fa-times"></i></span></li>
                            <li @click="detailedReport('dispositions', 'CDR', port.value, kk); toggleShowDispositions(port.value);" v-for="(disposition, kk) in reportSummary[`cdr-${port.value}`].dispositions" v-bind:key="disposition+Math.random()">
                                {{kk}} : {{disposition}}
                            </li>
                        </ul>
                    </td>
                    <td>{{reportSummary[`cdr-${port.value}`].uniqueCalls}}</td>
                    <!-- <td @click="detailedReport('repeatedCalls', 'CDR', port.value)">{{reportSummary[`cdr-${port.value}`].repeatedCalls}}</td> -->
                    <td>{{reportSummary[`numbers-${port.value}`].enabledNumbers}}</td>
                    <td>{{reportSummary[`numbers-${port.value}`].disabledNumbers}}</td>
                </tr>
            </template>
            <template v-else v-for="port in ports">
                <tr v-for="(v, k) in reportSummary[`cdr-${port.value}`]" v-bind:key="port.value+Math.random()" id="reportSummary" 
                    v-if="
                        (
                            !loading &&
                            (selectedPort.value == '' || selectedPort.value == undefined) 
                            && 
                            (
                                selectedFilter.value == '' || 
                                selectedFilter.value == 'SHOW_ALL' || 
                                (selectedFilter.value == 'HIDE_EMPTY_PORTS' && reportSummary[`cdr-${port.value}`][k].totalCalls > 0) ||
                                (selectedFilter.value == 'SHOW_EMPTY_PORTS' && reportSummary[`cdr-${port.value}`][k].totalCalls == 0)
                            )
                        ) 
                        || 
                        selectedPort.value == port.value
                    ">
                    <td>{{port.value}}</td>
                    <td>{{reportSummary[`cdr-${port.value}`][k].totalCalls}}</td>
                    <td>{{reportSummary[`cdr-${port.value}`][k].answeredCalls}}</td>
                    <td>{{reportSummary[`cdr-${port.value}`][k].failedCalls}}</td>
                    <td>{{reportSummary[`cdr-${port.value}`][k].busyCalls}}</td>
                    <td>{{reportSummary[`cdr-${port.value}`][k].noAnswerCalls}}</td>
                    <td>{{reportSummary[`cdr-${port.value}`][k].elseCalls}}</td>
                    <td>{{reportSummary[`cdr-${port.value}`][k].billmin}}</td>
                    <td>
                        Dispositions
                        <ul class="dispositions" v-if="showDipositions[`port-${port.value}`]"> 
                            <li class="close"><span><i class="fa fa-times"></i></span></li>
                            <li @click="detailedReport('dispositions', 'CDR', port.value,false , kk); toggleShowDispositions(port.value);" v-for="(disposition, kk) in reportSummary[`cdr-${port.value}`][k].dispositions" v-bind:key="disposition+Math.random()">
                                {{kk}} : {{disposition}}
                            </li>
                        </ul>
                    </td>
                    <td>{{reportSummary[`cdr-${port.value}`][k].uniqueCalls}}</td>
                    <!-- <td @click="detailedReport('repeatedCalls', 'CDR', port.value)">{{reportSummary[`cdr-${port.value}`].repeatedCalls}}</td> -->
                    <td>{{reportSummary[`numbers-${port.value}`][k].enabledNumbers}}</td>
                    <td>{{reportSummary[`numbers-${port.value}`][k].disabledNumbers}}</td>
                    <td>{{k}}</td>
                </tr>
            </template>
        </table>
        </div>
        </div>
     

    </div>
</template>

<script>
import DatePicker from 'vue2-datepicker';
import 'vue2-datepicker/index.css';
import 'vue-search-select/dist/VueSearchSelect.css'
import { ModelSelect } from 'vue-search-select'


import IndexComponent from './IndexComponent.vue';
import paginationComponent from './../global/paginationComponent';

export default {
    name : 'ReportComponent',
    props : {
        'url': {
            require : true,
            type : String
        },
        'urls': {
            require : true,
            type : Object
        },
        'reportSummaryData': {
            require : true,
            type : Object
        },
        'reportId' : {
            require : true,
        }
    },
    components : {
        DatePicker, IndexComponent, paginationComponent, 
        'model-select' : ModelSelect,
    },
 
    data(){
        return{
            reportTitle : '',
            exportType : '',
            enableDates : true,
            dates : [],

             ports : [],
            currentSort:'port',
            currentSortDir:'asc',
            showDipositions : {},
            selectedPort : {
                text : '',
                value: ''
            },
            ports : [],
            selectedFilter : {
                text : '',
                value : '',
            },
            filters : [
                {
                    text : 'Show All',
                    value : 'SHOW_ALL'
                },
                {
                    text : 'Hide Empty Ports',
                    value : 'HIDE_EMPTY_PORTS'
                },
                {
                    text : 'Show Empty Ports',
                    value : 'Show_EMPTY_PORTS'
                },
            ],

            showColumns : [
                {
                    name : 'Call Date' ,
                    column : 'calldate',
                    order : '',
                    if : false,
                },
                {
                    name : 'DST' ,
                    column : 'dst',
                    order : '',
                    if : false,
                },
                {
                    name : 'Disposition',
                    column : 'disposition' , 
                    order : '',
                    if : false,
                },
                {
                    name : 'Port',
                    column : 'port' , 
                    order : '',
                    if : false,
                },
                {
                    name : 'Bill Sec',
                    column : 'billsec',
                    order : '',
                    if : false,
                }
            ],  
            startDate : "",
            endDate : "",
            reportSummary : {},
            reportDetailes : [],
            actions : [],
            loading : false,
            showDetailed : false,
            pagination : {},
            perpage : 15,
            form : {
                date : '',
                action : '',
                type : '',
                store : false,
            }
        }
    },
    created(){
        this.init();
    },
    methods : {
        init(){
            //  - 3
            let date = new Date();
            date.setDate(date.getDate());
            let month = date.getUTCMonth() + 1; //months from 1-12
            let day = date.getUTCDate();
            let year = date.getUTCFullYear();
            this.startDate = `${year}-${month < 10 ? '0'+month : month}-${day}`;
            this.getPorts();
        },
        sort:function(s) {
            //if s == current sort, reverse
            if(s === this.currentSort) {
            this.currentSortDir = this.currentSortDir==='asc'?'desc':'asc';
            }
            this.currentSort = s;
        },
        toggleShowDispositions(port){
            this.showDipositions[`port-${port}`] = !this.showDipositions[`port-${port}`];
            this.$forceUpdate();
        },
        getPorts(){
            console.log(this.urls);
            axios.get(this.urls.getPortsUrl)
            .then(r => {
                this.ports = r.data.map(r => {
                    this.showDipositions[`port-${r.port}`] = false;
                    return {
                        value : r.port,
                        text : r.port,
                    };
                });
                console.log(r);
            }).catch(e => {
                alert(e);
            });
        },
        prepareGenerateForm(store = 'false'){
            this.form.date = this.startDate;
            this.form.endDate = this.endDate;
            this.form.action = 'GENERATE';
            this.form.type = 'SUMMARY';
            this.form.target = 'ALL';
            this.form.store = store;
            this.form.perpage = 0;
            this.form.offset = 0;
            this.form.reportId = this.reportId;
            this.form.title = this.reportTitle;
            this.form.ports = this.ports;
        },
        generateReport(){
            this.loading = true;
            axios.get(this.url, {
                params : {
                    ...this.form
                }
            })
            .then(r => {
                this.loading = false;
                this.reportSummary = r.data.content;
                console.log(r);
            }).catch(e => {
                alert(e);
            });
        },
        getDetailedReport(perpage, offset){
            this.form.perpage = perpage;
            this.form.offset = offset;
            axios.get(this.url, {
                params : {
                    ...this.form
                }
            })
            .then(r => {
                this.showDetailed = true;
                this.setShowColumns();
                this.reportDetailes = r.data.content;
                this.pagination = r.data.pagination;
                console.log(r);
            }).catch(e => {
                alert(e);
            });
        },
        detailedReport(section, target, port, key=false, date=false){
            this.form.action = 'GENERATE';
            this.form.type = 'DETAILED';
            this.form.target = target;
            this.form.withDates = this.useDates;
            this.form.section = section;
            this.form.reportId = this.reportId;
            this.form.key = key;
            this.form.ports = [{value:port}];
            if(date){
                this.form.date = date;
                if(this.form.endDate.split(' ')[1] != undefined){
                    if(date.split(' ')[0] != this.form.endDate.split(' ')[0]){
                        this.form.endDate = '';
                    }
                }
            }
            this.getDetailedReport(this.perpage,0);
        },
        setShowColumns(){
            if(this.form.target == 'CDR'){
                this.showColumns = [
                    {
                        name : 'Call Date' ,
                        column : 'calldate',
                        order : '',
                        if : false,
                    },
                    {
                        name : 'DST' ,
                        column : 'dst',
                        order : '',
                        if : false,
                    },
                    {
                        name : 'Disposition',
                        column : 'disposition' , 
                        order : '',
                        if : false,
                    },
                    {
                        name : 'Port',
                        column : 'port' , 
                        order : '',
                        if : false,
                    },
                    {
                        name : 'Bill Sec',
                        column : 'billsec',
                        order : '',
                        if : false,
                    },
                    {
                        name : 'Bill Min',
                        column : 'billmin',
                        order : '',
                        if : false,
                    }
                ];
                if(this.form.section == 'repeatedCalls' || this.form.section == 'uniqueCalls'){
                    this.showColumns = [
                        {
                            name : 'Repeated Calls',
                            column : 'repeatedCalls' , 
                            order : '',
                            if : false,
                        },
                        {
                            name : 'Number',
                            column : 'number' , 
                            order : '',
                            if : false,
                        }
                    ];
                }
            }else if(this.form.target == 'ROUTES'){
                this.showColumns = [
                    {
                        name : 'ID' ,
                        column : 'id',
                        order : '',
                        if : false,
                    },
                    {
                        name : 'Port',
                        column : 'port' , 
                        order : '',
                        if : false,
                    },
                    {
                        name : 'Number' ,
                        column : 'number',
                        order : '',
                        if : false,
                    },
                    {
                        name : 'Date Enabled',
                        column : 'DateEnabled' , 
                        order : '',
                        if : false,
                    },
                    {
                        name : 'Date Disabled',
                        column : 'DateDisabled' , 
                        order : '',
                        if : false,
                    }
                ];
            }else if(this.form.target == 'PORTS'){
                this.showColumns = [
                    {
                        name : 'Port',
                        column : 'port' , 
                        order : '',
                        if : false,
                    },
                    {
                        name : 'Date Enabled',
                        column : 'dateenabled' , 
                        order : '',
                        if : false,
                    },
                    {
                        name : 'Date Disabled',
                        column : 'datedisabled' , 
                        order : '',
                        if : false,
                    }
                ];
            }
        },
        PaginationMove(data){
            this.getDetailedReport(data.perpage, data.offset);
        },
        store(){
            this.prepareGenerateForm('true');
            this.generateReport();
        }
    },
    watch : {
        startDate(){
            if(this.ports.length > 0){
                this.prepareGenerateForm();
                this.generateReport();
            }
        },
        endDate(){
            if(this.ports.length > 0){
                this.prepareGenerateForm();
                this.generateReport();
            }
        },
        ports(){
            this.prepareGenerateForm();
            this.generateReport();
        },
        reportSummaryData(){
            this.reportSummary = this.reportSummaryData;
        }
    },
    computed : {
        updatedPorts(){
            return this.ports.filter(port => {
                return 
                (
                    !this.loading && 
                    this.selectedPort.value == '' 
                    && 
                    (
                        this.selectedFilter.value == '' || 
                        this.selectedFilter.value == 'SHOW_ALL' || 
                        (this.selectedFilter.value == 'HIDE_EMPTY_PORTS' && this.reportSummary[`cdr-${port.value}`].totalCalls > 0) ||
                        (this.selectedFilter.value == 'SHOW_EMPTY_PORTS' && this.reportSummary[`cdr-${port.value}`].totalCalls == 0)
                    )
                ) 
                || 
                this.selectedPort.value == port.value
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
#ReportComponent{
    padding : 30px 0px;
    .dispositions{
        display: inline-block;
        position: absolute;
        background-color: black;
        padding: 20px;
        border-radius: 10px;
        list-style: none;
        .close{
            color : red;
            text-align : right;
            font-size : 40px;   
        }
    }
    #reportSummary{
        td{
            cursor: pointer;
        }
    }
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
        transition: all 0.5s ease-in-out;
        max-height: 100%;
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

    /******************alerts section */
    #alertsContainer{
        position: fixed;
        z-index : 10000000000;
        left : 10px;
        bottom : 10px;
        width : 300px;
    }

    #addForm{
        .row{
            max-height: 100vh;
            overflow : auto;
        }
        .col-md-10{
            padding : 5vh 0px;
        }
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