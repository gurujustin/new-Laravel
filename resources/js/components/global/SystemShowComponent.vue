<template>
    <div id="addComponent">
        <div class="card text-white bg-dark">
            <div class="card-header">{{showDetails.name}} {{item[showDetails.column]}}</div>

            <div class="card-body">
                <div class="report" v-for="cc in columns" v-bind:key="cc.id">
                    <template v-if="cc.type == 'report'">
                        <div v-html="handleHtml(item[cc.column] , cc)"></div>
                    </template>
                </div>

                <table class="table table-dark table-bordered">
                    <tbody>
                        <tr v-for="c in columns" v-bind:key="c.id">
                            <template v-if="c.type != 'report'">
                                <th>{{c.name}}</th>
                                <td v-html="handleHtml(item[c.column] , c)"></td>
                            </template>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>

    </div>
</template>

<script>
export default {
    name : 'ShowComponent' ,
    props : {
        'item' : {
            type : Object ,
            required : true, 
        },
        'showDetails' : {
            type : Object , 
            required : true,
        },
        "columns" : {
            type : Array , 
            require : true,
        }
    },
    data(){
        return{
  
        }
    },
    created(){
        this.init();
    },
    methods : {
        init(){

        },
        handleHtml(text , column){
            let htmlData = '';
            if(column.type == 'sub'){
                htmlData = text[column.subColumn];

            }else if(column.type == 'report'){
                let data = JSON.parse(text);
                return this.handleReport(data);
            }else{
                if(text != null){
                    let lines = text.split('___');
                    
                    for(let x = 0; x < lines.length ; x++){
                        if(x > 0){
                            htmlData += '<br />';
                        }
                        let spaces = lines[x].split('__');
                        for(let y = 0; y < spaces.length ; y++){
                            if(y > 0){
                                htmlData += "<span class='space'></span>";
                            }
                            let result;
                            if(column.type == 'time'){
                                result = this.secondsToTime(parseInt(spaces[y]));
                            }else{
                                result = spaces[y];
                            }
                            htmlData += result;
                        }
                    }
                }
            }

            return htmlData;
        },
        secondsToTime(totalSeconds){
            let hours = Math.floor(totalSeconds / 3600);
            totalSeconds %= 3600;
            let minutes = Math.floor(totalSeconds / 60);
            let seconds = totalSeconds % 60;

            return `h ${hours}:${minutes}:${seconds}`;
        },
        handleReport(data){
            let htmlData = '';
            for(let x in data){
                let report = data[x]['report'];
                let type = data[x]['type'];
                if(type == 'list'){
                    let listedData = '<h3 class="title text-center">'+x+'</h3><ul class="list-unstyled">';
                    for(let y in report){
                        if(y != 'compare_hash'){
                            listedData += `<li class="row"><span class="col-md-2">${y}</span><span class="col-md-10">${report[y]}</span>`;
                        }
                    }
                    listedData += "</ul>";
                    htmlData += '<div class="section">'+listedData+'</div>';     
                }else if(type == 'table'){
                    let headers = `<thead><tr>`;
                    let allHeaders = [];
                    let lastLength = 0;

                    Object.size = function(obj) {
                        var size = 0, key;
                        for (key in obj) {
                            if (obj.hasOwnProperty(key)) size++;
                        }
                        return size;
                    };

                    let filtered = report.filter(item =>{
                        if(Object.size(item) > lastLength){
                            lastLength = Object.size(item);
                            return true;
                        }
                        return false;
                    });

                    while(Object.size(filtered) > 1){
                        filtered = report.filter(item =>{
                            if(Object.size(item) > lastLength){
                                lastLength = Object.size(item);
                                return true;
                            }
                            return false;
                        });
                    }


                    for(let h in filtered[0]){
                        if(h != 'compare_hash'){
                            headers += `<th>${h}</th>`;
                            allHeaders.push(h);
                        }
                    }
                    headers += '</tr></thead>';
                    let results = '<tbody>';

                    for(let xx = 0; xx < report.length; xx++){
                        results += `<tr>`;
                        for(let h = 0; h < allHeaders.length ; h++){
                            let head = allHeaders[h];
                            if(head != 'compare_hash'){
                                results += `<td>${report[xx][head] != undefined ? report[xx][head] : ''}</td>`;
                            }
                        }
                        results += '</tr>';
                    }

                    results += '</tbody>';

                    let table = `<h3 class="title text-center">${x}</h3><table class="table table-dark table-bordered table-responsive">${headers}${results}</table>`;

                    htmlData += '<div class="section">'+table+'</div>';     
                }
            }
            return htmlData;
        }  

    }
}
</script>


<style lang="scss">
#addComponent{
    z-index : 1000;
    max-height : 80vh;
    overflow : auto;
    .space{
        display : inline-block;
        margin-right : 20px;
    }
    .last-update{
        font-size: 12px;
        display: inline-block;
        padding: 10px;
        margin-left: 20px;
        color: #1ec459;
    }
    .report{
        .section{
            .title{
                margin-bottom: 10px;
            }
            ul{
                li{
                    border-bottom : 1px solid #888;
                    padding : 5px;
                    margin : 0px 15px;
                    margin-bottom : 10px;
                    .col-md-2,.col-md-10{
                        padding : 0px;
                    }
                }
            }
        }
    }
}
</style>
