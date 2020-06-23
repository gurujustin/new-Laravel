<template>
  <div id="NumbersData">
    <div class="card text-white bg-dark">
        <div class="card-header">Quick Numbers Details</div>
        <div class="card-body">
            <table class="table table-dark table-bordered">
                <tbody>
                    <tr>
                        <th col="3">Total Called Unique Numbers</th>
                        <td col="3">{{summaryData.uniqueNumbers}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <br>

    <div class="card text-white bg-dark">
        <div class="card-header">
            <div class="row">
                <div class="col-md-3 title">
                    Top Called Numbers [{{summaryData.topCalledAverage}}]
                </div>
                <div class="col-md-1">
                    <input v-model="formTopCalls.topCalledNumbersCount" type="number" placeholder="Show Top Called Numbers..." class="form-control"/>
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" v-model="formTopCalls.search" placeholder="Search By Number">
                </div>
                <div class="col-md-3">
                    <date-picker
                        v-model="formTopCalls.dateFrom"
                        type="datetime"
                        valueType="format"
                        placeholder="Select date From"
                    ></date-picker>
                </div>
                <div class="col-md-3">
                    <date-picker
                        v-model="formTopCalls.dateTo"
                        type="datetime"
                        valueType="format"
                        placeholder="Select date To"
                    ></date-picker>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-dark table-bordered">
                <tbody>
                    <tr>
                        <th>Number</th>
                        <td>Number Of Repeated Calls</td>
                        <td>Daily Calls Average</td>
                    </tr>
                    <tr v-for="dst in summaryData.topCalled" v-bind:key="dst.dst">
                        <th col="3" @click="getDetails(dst.dst)" style="cursor: pointer;">{{dst.dst}}</th>
                        <td col="3">{{dst.repeated}}</td>
                        <td>
                            {{Math.round(parseInt(dst.repeated) / summaryData.topCalledAverage)}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <br>

    <!-- src part -->
    <div class="card text-white bg-dark">
        <div class="card-header">
            <div class="row">
                <div class="col-md-3 title">
                    Top Callers [{{summaryData.topCallersAverage}}]
                </div>
                <div class="col-md-1">
                    <input v-model="formTopCallers.topCalledNumbersCount" type="number" placeholder="Show Top Called Numbers..." class="form-control"/>
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" v-model="formTopCallers.searchCallers" placeholder="Search By Src">
                </div>
                <div class="col-md-3">
                    <date-picker
                        v-model="formTopCallers.dateFromCallers"
                        type="datetime"
                        valueType="format"
                        placeholder="Select date From"
                    ></date-picker>
                </div>
                <div class="col-md-3">
                    <date-picker
                        v-model="formTopCallers.dateToCallers"
                        type="datetime"
                        valueType="format"
                        placeholder="Select date To"
                    ></date-picker>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-dark table-bordered">
                <tbody>
                    <tr>
                        <th>Source</th>
                        <td>Repeated Callers</td>
                        <td>Daily Calls Average</td>
                    </tr>
                    <tr v-for="src in summaryData.topCallers" v-bind:key="src.src">
                        <th col="3" @click="getDetailsCallers(src.src)" style="cursor: pointer;">{{src.src}}</th>
                        <td col="3">{{src.repeated}}</td>
                        <td>
                            {{Math.round(parseInt(src.repeated) / summaryData.topCallersAverage)}}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <br>

    <!-- enabled and disabled numbers -->
    <div class="card text-white bg-dark">
        <div class="card-header">
            <div class="row">
                <div class="col-md-3 title">
                    Enabled/Disabled Numbers
                </div>
                <!-- <div class="col-md-1">
                    <input v-model="formEnabledNumbers.topCalledNumbersCount" type="number" placeholder="Show Top Called Numbers..." class="form-control"/>
                </div> -->
                <div class="col-md-3">
                    <input type="text" class="form-control" v-model="formEnabledNumbers.searchNumbers" placeholder="Search By Port">
                </div>
                <div class="col-md-3">
                    <date-picker
                        v-model="formEnabledNumbers.dateFromNumbers"
                        type="datetime"
                        valueType="format"
                        placeholder="Select date From"
                    ></date-picker>
                </div>
                <div class="col-md-3">
                    <date-picker
                        v-model="formEnabledNumbers.dateToNumbers"
                        type="datetime"
                        valueType="format"
                        placeholder="Select date To"
                    ></date-picker>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-dark table-bordered">
                <tbody>
                    <tr>
                        <th>Port</th>
                        <td>Enabled Numbers</td>
                        <td>Disabled Numbers</td>
                    </tr>
                    <tr v-for="port in summaryData.enabledNumbers" v-bind:key="port.port">
                        <th col="3" @click="getDetailsNumbers(port.port)" style="cursor: pointer;">{{port.port}}</th>
                        <td col="3">{{port.enabled}}</td>
                        <td col="3">{{port.disabled}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>


    <!-- show Component -->
    <transition name="addform">
        <div id="addForm" v-if="showComponent">
            <div class="row">
                <div class="col-md-1" @click="showComponent = false"></div>
                <div class="col-md-10">
                    <IndexComponent @orderIndex="orderIndex" @useItem="useItem" :columns="showColumns" :actions="actions" :users="detailed" @showData="show" />
                    <!-- pagination -->
                    <div id="pagination">
                        <paginationComponent :pagination="pagination" @changePage="PaginationMove"></paginationComponent>
                    </div>
                </div>
                <div class="col-md-1" @click="showComponent = false"></div>
            </div>
            <div class="overlay" @click="showComponent = false"></div>
        </div>
    </transition>

  </div>
</template>

<script>
import DatePicker from 'vue2-datepicker';
import 'vue2-datepicker/index.css';
import IndexComponent from './../global/IndexComponent.vue';
import paginationComponent from './../global/paginationComponent';

export default {
    name : "NumbersSummary",
    props : {
        "data" : {
            require : true,
        },
        "urls" : {
            require : true,
        }
    },
    components : {
        'date-picker' : DatePicker,
        'IndexComponent' : IndexComponent,
        'paginationComponent' : paginationComponent,
    },
    data(){
        return{
            summaryData : this.data,
            showComponent : false,
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
                    name : 'Port',
                    column : 'port' , 
                    order : '',
                    if : false,
                }
            ],  
            actions : [],
            detailed : [],
            activeDetailed : '',
            formTopCalls : {
                target : 'topCalled',
                type : '',
                perpage : 20,
                offset : 0,
                topCalledNumbersCount : 20,
                search : '',
                api : true,
                dateFrom : '',
                dateTo : '',
            },
            formTopCallsDetails : {
                target : 'topCalled',
                type : '',
                perpage : 20,
                offset : 0,
                topCalledNumbersCount : 20,
                search : '',
                api : true,
                dateFrom : '',
                dateTo : '',
            },
            formTopCallers : {
                target : 'topCallers',
                type : '',
                perpage : 20,
                offset : 0,
                topCalledNumbersCount : 20,
                searchCallers : '',
                api : true,
                dateFromCallers : '',
                dateToCallers  : '',
            },
            formTopCallersDetails : {
                target : 'topCallers',
                type : '',
                perpage : 20,
                offset : 0,
                topCalledNumbersCount : 20,
                searchCallers : '',
                api : true,
                dateFromCallers : '',
                dateToCallers : '',
            },
            formEnabledNumbers : {
                target : 'enabledNumbers',
                type : '',
                perpage : 20,
                offset : 0,
                enabledNumbersCount : 20,
                searchNumbers : '',
                api : true,
                dateFromNumbers : '',
                dateToNumbers  : '',
            },
            formTopNumbersDetails : {
                target : 'enabledNumbers',
                type : '',
                perpage : 20,
                offset : 0,
                topCalledNumbersCount : 20,
                searchNumbers : '',
                api : true,
                dateFromNumbers : '',
                dateToNumbers : '',
            },
            pagination : {

            },
        }
    },
    methods : {
        getData(){
            axios.get(this.urls.searchUrl , {
                params : {...this.formTopCalls,...this.formTopCallers, ...this.formEnabledNumbers},
            }).then(r => {
                if(r.data[this.formTopCalls.target]){
                    this.summaryData[this.formTopCalls.target] = r.data[this.formTopCalls.target];
                }
                if(r.data[this.formTopCallers.target]){
                    this.summaryData[this.formTopCallers.target] = r.data[this.formTopCallers.target];
                }

                if(r.data[this.formEnabledNumbers.target]){
                    this.summaryData[this.formEnabledNumbers.target] = r.data[this.formEnabledNumbers.target];
                }
            }).catch(e => {
                console.log(e);
            })
        },
        getDetails(number = false, perpage = false, offset = false){
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
                    name : 'Port',
                    column : 'port' , 
                    order : '',
                    if : false,
                }
            ];

            this.activeDetailed = 'getDetails';
            if(number){
                this.formTopCallsDetails.search = number;
            }
            this.formTopCallsDetails = {...this.formTopCalls, perapge : perpage ? perpage !== false : this.formTopCallsDetails.perapge, offset : offset !== false ? offset : this.formTopCallsDetails.offset, 
            type : 'DETAILED', search : this.formTopCallsDetails.search };

            axios.get(this.urls.searchUrl , {
                params : {...this.formTopCallsDetails},
            }).then(r => {
                this.showComponent = true;
                this.detailed = r.data[this.formTopCallsDetails.target];
                this.pagination = r.data.pagination;
                this.$forceUpdate();
                console.log(r.data);
            }).catch(e => {
                console.log(e);
            })
        },
        getDetailsCallers(number = false, perpage = false, offset = false){
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
                    name : 'Port',
                    column : 'port' , 
                    order : '',
                    if : false,
                }
            ];

            this.activeDetailed = 'getDetailsCallers';
            if(number){
                this.formTopCallersDetails.searchCallers = number;
            }
            this.formTopCallersDetails = {...this.formTopCallers, perapge : perpage ? perpage !== false : this.formTopCallersDetails.perapge, 
                offset : offset !== false ? offset : this.formTopCallersDetails.offset, type : 'DETAILED',
                searchCallers : this.formTopCallersDetails.searchCallers };

            axios.get(this.urls.searchUrl , {
                params : {...this.formTopCallersDetails},
            }).then(r => {
                this.showComponent = true;
                this.detailed = r.data[this.formTopCallersDetails.target];
                this.pagination = r.data.pagination;
                this.$forceUpdate();
                console.log(r.data);
            }).catch(e => {
                console.log(e);
            })
        },
        getDetailsNumbers(number = false, perpage = false, offset = false){
            this.showColumns = [
                {
                    name : 'Port' ,
                    column : 'port',
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

            this.activeDetailed = 'getDetailsNumbers';
            if(number){
                this.formTopNumbersDetails.searchNumbers = number;
            }
            this.formTopNumbersDetails = {...this.formEnabledNumbers, perapge : perpage ? perpage !== false : this.formTopNumbersDetails.perapge, 
                offset : offset !== false ? offset : this.formTopNumbersDetails.offset, type : 'DETAILED',
                searchNumbers : this.formTopNumbersDetails.searchNumbers };

            axios.get(this.urls.searchUrl , {
                params : {...this.formTopNumbersDetails},
            }).then(r => {
                this.showComponent = true;
                this.detailed = r.data[this.formTopNumbersDetails.target];
                this.pagination = r.data.pagination;
                this.$forceUpdate();
                console.log(r.data);
            }).catch(e => {
                console.log(e);
            })
        },

        PaginationMove(data){
            this[this.activeDetailed](false, data.perpage, data.offset);
        },
        orderIndex(data){
            console.log(data);
        },
        useItem(data){
            console.log(data);
        },
        show(data){
            console.log(data);
        }
    },
    watch : {
        formTopCalls : {
            handler(){
                this.getData();
            },
            deep : true,
        },
        formTopCallers : {
            handler(){
                this.getData();
            },
            deep : true,
        } ,
        formEnabledNumbers : {
            handler(){
                this.getData();
            },
            deep : true,
        }  
    }
}
</script>


<style lang="scss" scoped>
#NumbersData{
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