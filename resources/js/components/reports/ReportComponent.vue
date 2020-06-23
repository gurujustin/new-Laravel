<template>
    <div id="ReportComponent">
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
                    
                    <div class="col-md-1">
                        <div class="btn btn-success">Store</div>
                    </div>
                    
                    <div class="col-md-2">
                        <input type="text" v-model="reportTitle" placeholder="Report Title" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <select class="form-control" v-model="exportType">
                            <option value="PDF">PDF</option>
                            <option value="XSLX">XSLX</option>
                            <option value="CSV">CSV</option>
                        </select>
                    </div>
                    
                    <div class="col-md-1">
                        <button class="btn btn-success">Export</button>
                    </div>
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
                    <div class="col-md-1" @click="showDetailed"></div>
                </div>

                <div class="overlay" @click="showDetailed"></div>
            </div>
        </transition>

        <div id="reportSummary" v-if="reportSummary.cdr && !loading">
            <table class="table table-dark table-bordered table-hover table-striped">
                <tr @click="detailedReport('totalCalls', 'CDR')"><td>Total Calls : {{reportSummary.cdr.totalCalls}}</td></tr>
                <tr @click="detailedReport('answeredCalls', 'CDR')"><td>Total Answered : {{reportSummary.cdr.answeredCalls}}</td></tr>
                <tr @click="detailedReport('failedCalls', 'CDR')"><td>Total Failed : {{reportSummary.cdr.failedCalls}}</td></tr>
                <tr @click="detailedReport('busyCalls', 'CDR')"><td>Total Busy : {{reportSummary.cdr.busyCalls}}</td></tr>
                <tr @click="detailedReport('noAnswerCalls', 'CDR')"><td>Total No Answer : {{reportSummary.cdr.noAnswerCalls}}</td></tr>
                <tr @click="detailedReport('elseCalls', 'CDR')"><td>Total Else Status : {{reportSummary.cdr.elseCalls}}</td></tr>
                <tr @click="detailedReport('repeatedCalls', 'CDR')"><td>Total Repeated Calls : {{reportSummary.cdr.repeatedCalls}}</td></tr> 

                <tr @click="detailedReport('enabledNumbers', 'ROUTES')"><td>Total Numbers Enabled : {{reportSummary.numbers.enabledNumbers}}</td></tr>
                <tr @click="detailedReport('disabledNumbers', 'ROUTES')"><td>Total Numbers Disabled : {{reportSummary.numbers.disabledNumbers}}</td></tr>

                <tr @click="detailedReport('totalPorts', 'PORTS')"><td>Total Ports : {{reportSummary.ports.totalPorts}}</td></tr>
                <tr @click="detailedReport('disabledPorts', 'PORTS')"><td>Total Enabled Ports : {{reportSummary.ports.disabledPorts}}</td></tr>
                <tr @click="detailedReport('enabledPorts', 'PORTS')"><td>Total Disabled Ports : {{reportSummary.ports.enabledPorts}}</td></tr> 
            </table>
        </div>
    </div>
</template>

<script>
import DatePicker from 'vue2-datepicker';
import 'vue2-datepicker/index.css';

import IndexComponent from './IndexComponent.vue';
import paginationComponent from './../global/paginationComponent';

export default {
    name : 'ReportComponent',
    props : {
        'url': {
            require : true,
            type : Object
        }
    },
    components : {
        DatePicker, IndexComponent, paginationComponent
    },
    data(){
        return{
            reportTitle : '',
            exportType : '',
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
            let date = new Date();
            date.setDate(date.getDate() );
            let month = date.getUTCMonth() + 1; //months from 1-12
            let day = date.getUTCDate();
            let year = date.getUTCFullYear();
            this.startDate = `${year}-${month < 10 ? '0'+month : month}-${day}`;
        },
        prepareGenerateForm(){
            this.form.date = this.startDate;
            this.form.endDate = this.endDate;
            this.form.action = 'GENERATE';
            this.form.type = 'SUMMARY';
            this.form.target = 'ALL';
            this.form.store = 'false';
            this.form.perpage = 0;
            this.form.offset = 0;
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
        detailedReport(section, target){
            this.form.action = 'GENERATE';
            this.form.type = 'DETAILED';
            this.form.target = target;
            this.form.section = section;
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
                    }
                ];
                if(this.form.section == 'repeatedCalls'){
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
        }
    },
    watch : {
        startDate(){
            this.prepareGenerateForm();
            this.generateReport();
        },
        endDate(){
            this.prepareGenerateForm();
            this.generateReport();
        }
    }
}
</script>

<style lang="scss" scoped>
#ReportComponent{
    padding : 30px 0px;
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