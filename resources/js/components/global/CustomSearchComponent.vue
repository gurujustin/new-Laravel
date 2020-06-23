<template>
    <div id="addComponent">

        <div class="card text-white bg-dark">
            <div class="card-body">

                <div class="form-group">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="column">Select Column</label>
                        </div>
                        <div class="col-md-8">
                            <select v-model="AddedColumn" id="column" class="form-control" @change="addColumn()">
                                <option :value="c.searchColumn ? c.searchColumn : c.column" v-bind:key="c.column + Math.random()" v-for="c in columns">
                                    {{c.name}}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-1 add_column" @click="addColumn()">
                            <i class="fa fa-plus fa-fw"></i>
                        </div>
                    </div>
                </div>

                <ol>
                    <li class="form-group" v-for="(column) in selectedColumns" v-bind:key="column.id">
                        <div class="row">
                            <div class="col-md-3">
                                <label :for="column.column">{{column.column}}</label>
                            </div>

                            <template v-if="column.items != undefined">
                                <div class="col-md-4">
                                    <select type="text" :id="column.column" v-model="column.value" class="form-control">
                                        <option 
                                        :value="item[column.key != undefined ? column.key : 'id']" 
                                        v-bind:key="item[column.key != undefined ? column.key : 'id'] + Math.random()" 
                                        v-for="item in filteredItems(column.items , column)">
                                            {{item[column.show ? column.show : 'name']}}
                                        </option>
                                    </select>
                                </div>
                            </template>


                            <template v-else>
                                <div class="col-md-4">
                                    <input type="text" :id="column.column" v-model="column.value" class="form-control" placeholder="Write Column Value">
                                </div>
                            </template>

                            <div class="col-md-4">
                                <select id="operator" v-model="column.operator" class="form-control">
                                    <option :value="operator.value" v-bind:key="operator.value + Math.random()" v-for="operator in operators">
                                        {{operator.name}}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-1 add_column" @click="removeColumn(column.id)">
                                <i class="fa fa-times fa-fw"></i>
                            </div>
                        </div>
                    </li>
                </ol>

                <div class="form-group row mb-0">
                    <div class="col">
                        <a class="btn btn-success btn-block" @click.prevent="$emit('search' , url)" :href="url" target="_blank">
                            Search Data <i class="fa fa-file-export"></i>
                        </a>
                    </div>
                    <div class="col">
                        <input type="text" class="form-control" v-model="filename">
                    </div>
                    <div class="col">
                        <select v-model="type" class="form-control">
                            <option :value="v.id" v-bind:key="v.id" v-for="v in types">{{v.title}}</option>
                        </select>
                    </div>
                    <div class="col">
                        <a :href="url+'&filename='+filename+'.'+type+'&exportCustomSearch=1'" class="btn btn-success" target="_blank">Export</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- <pre style="color:#FFF;">
            <code>
                {{nums}}
            </code>

            <code>
                {{selectedColumns}}
            </code>

        </pre> -->
    </div>
</template>
<script>
export default {
    name : 'export-component' ,
    props : {
        "exportUrl" : {
            required : true,
        },
        // 'states' : {
        //     require : true,
        // },
        // 'files' : {
        //     require : true,
        // },
        'columns' : {
            require : true,
        }
    },
    data(){
        return {
            form : {},
            url : '',
            selectedColumns : [] , 
            AddedColumn : '',
            operators : [
                {
                    name : 'Equal' , 
                    value : '='
                },
                {
                    name : 'Not Equal' , 
                    value : '!='
                },
                {
                    name : 'Like' , 
                    value : 'LIKE'
                },
                {
                    name : 'Bigger Than' , 
                    value : '>'
                },
                {
                    name : 'Bigger Than Or Equal' , 
                    value : '>='
                },
                {
                    name : 'Lower Than' , 
                    value : '<'
                },
                {
                    name : 'Lower Than Or Equal' , 
                    value : '<='
                }
            ],
            types : [
                {
                    title : 'Excel' , 
                    id : 'xlsx'
                },
                {
                    title : 'CSV' , 
                    id : 'csv'
                },
                {
                    title : 'HTML' , 
                    id : 'html'
                },
                {
                    title : 'PDF' , 
                    id : 'pdf'
                }
            ],
            filename : '',
            type : 'csv',
            exportLink : '',
            nums : {},
            num : '00000001',
        }
    },
    created(){
        this.init();
    },
    methods : {
        init(){
            this.form = {
                database : this.database, 
                state : '', 
                category : '', 
                all : 0,
                isEmptyPhone : false,
            };
        },
        generateUrl(){
            let query = '';
            this.selectedColumns.map((item , index) => {
                query += `${item.column+'__'+item.value+item.num+'__'+item.operator}`;
                if(index + 1 < this.selectedColumns.length){
                    query += '||';
                }    
            });
            this.url = this.exportUrl+`?search=${query}&api=1&customSearch=1`;
        },
        addColumn(){
            let item = {}; 
            let col = this.columns.filter(item => {
                return item.column == this.AddedColumn || item.searchColumn == this.AddedColumn;
            })[0];

            for(let x in col){
                item[x] = col[x];
            }

            item['column'] = this.AddedColumn;
            item['operator'] ='=';
            item['operator'] ='';
            item['num'] ='';
            item['id'] = Math.random() * 10;

            this.selectedColumns.push(item);
        },
        updateValue(e, index){
            let value = e.target.value;
            this.selectedColumns[index].value = value;
            this.$forceUpdate();
        },
        removeColumn(id){
            this.selectedColumns = this.selectedColumns.filter(item => {
                return item.id != id;
            });
        },
        addNumColumn(id){
            this.selectedColumns = this.selectedColumns.map(item => {
                if(item.id == id){
                    if(!this.nums[id]){
                        item.num = this.num;
                    }else{
                        item.num = '';
                    }
                    return item;
                }
                return item;
            });
        },
        filteredItems(items, column){
            if(column.filter){
                let filters = column.filter.split(',');
                let filteredItem = [];
                for(let yy = 0; yy < this.selectedColumns.length; yy++){
                    for(let x = 0; x < filters.length; x++){
                        if(this.selectedColumns[yy].column == filters[x]){
                            filteredItem.push(JSON.parse(JSON.stringify(this.selectedColumns[yy])));
                        }
                    }
                }
                // let filteredItem = JSON.parse(JSON.stringify(this.selectedColumns)).filter(item => {
                //     for(let x = 0; x<filters.length ; x++){
                //         return item.column = filters[x];
                //     }
                // });
                
                // console.log(filteredItem);

                if(filteredItem.length > 0 && filteredItem[0].value != undefined){
                    let filteredData = items.filter(item => {
                        let status = true;
                        for(let x = 0; x < filteredItem.length; x++){
                            for(let y = 0; y < filters.length; y++){
                                if(filters[y] == filteredItem[x].column){
                                    if(status){
                                        status = item[filters[y]] == filteredItem[x].value;
                                    }
                                }
                            }
                        }
                        return status;
                    });

                    // console.log(filteredData);

                    return filteredData;
                }else{
                    return items;
                }

            }else{
                return items;
            }
        }
    },
    watch : {
        form: {
            handler: function(newValue) {
                this.generateUrl();
            },
            deep: true
        },
        selectedColumns: {
            handler: function(newValue) {
                this.generateUrl();
            },
            deep: true
        }
    }
}
</script>

<style lang="scss" scoped>
#addComponent{
    z-index : 1000;
    max-height : 80vh;
    overflow : auto;
    .add_column{
        cursor : pointer;
    }
    ol{
        padding : 0px;
    }
}
</style>