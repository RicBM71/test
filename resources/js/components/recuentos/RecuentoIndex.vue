<template>
    <div>
        <loading :show_loading="show_loading"></loading>
           <div v-if="registros && status == false">
                <my-dialog :dialog.sync="dialog" registro="registro" @destroyReg="destroyReg"></my-dialog>
                <v-card>
                    <v-card-title>
                        <h2>{{titulo}}</h2>
                        <v-spacer></v-spacer>
                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }">
                                <v-btn
                                    v-on="on"
                                    color="white"
                                    icon
                                    @click="goRestaurar"
                                >
                                    <v-icon color="orange darken-4">add_task</v-icon>
                                </v-btn>
                            </template>
                                <span>Restaurar bajas y en recuento</span>
                        </v-tooltip>
                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }">
                                <v-btn
                                    v-on="on"
                                    color="white"
                                    icon
                                    @click="goBajaPerdidas"
                                >
                                    <v-icon color="red darken-4">gavel</v-icon>
                                </v-btn>
                            </template>
                                <span>Dar de baja perdidas</span>
                        </v-tooltip>
                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }">
                                <v-btn
                                    v-on="on"
                                    color="white"
                                    icon
                                    @click="goEstados"
                                >
                                    <v-icon color="primary">dvr</v-icon>
                                </v-btn>
                            </template>
                                <span>Estados recuento</span>
                        </v-tooltip>
                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }">
                                <v-btn
                                    v-on="on"
                                    color="white"
                                    icon
                                    @click="filtro = !filtro"
                                >
                                    <v-icon color="primary">filter_list</v-icon>
                                </v-btn>
                            </template>
                            <span>Filtros</span>
                        </v-tooltip>
                        <v-tooltip bottom>
                        <template v-slot:activator="{ on }">
                            <v-btn
                                v-show="items.length > 0 && hasExcel"
                                v-on="on"
                                color="white"
                                icon
                                @click="goExcel()"
                            >
                                <v-avatar size="32px">
                                    <img class="img-fluid" src="/assets/excel.png">
                                </v-avatar>
                            </v-btn>
                        </template>
                        <span>Exportar a Excel</span>
                    </v-tooltip>
                        <menu-ope></menu-ope>
                    </v-card-title>
                </v-card>
                <v-card v-show="filtro">
                    <filtro-rec :filtro.sync="filtro" :items.sync="items"></filtro-rec>
                </v-card>
                <v-card>
                    <v-container>
                        <v-layout row wrap>
                            <v-flex xs6></v-flex>
                            <v-flex xs6>
                                <v-spacer></v-spacer>
                                <v-text-field
                                    v-model="search"
                                    append-icon="search"
                                    label="Buscar"
                                    single-line
                                    hide-details
                                ></v-text-field>
                            </v-flex>
                        </v-layout>
                        <br/>
                        <v-layout row wrap>
                            <v-flex xs12>
                                <v-data-table
                                    :headers="headers"
                                    :items="items"
                                    :search="search"
                                    @update:pagination="updateEventPagina"
                                    :pagination.sync="pagination"
                                    rows-per-page-text="Registros por p??gina"
                                >
                                    <template slot="items" slot-scope="props">
                                        <td>{{props.item.referencia }}</td>
                                        <td v-if="props.item.deleted_at == null">{{ props.item.nombre }}</td>
                                        <td v-else class="tachado">{{ props.item.nombre }}</td>
                                        <td class="text-xs-right">{{ props.item.peso_gr| currency('', 2, { thousandsSeparator:'.', decimalSeparator: ',', symbolOnLeft: false })}}</td>
                                        <td class="text-xs-right">{{ props.item.precio_coste | currency('???', 2, { thousandsSeparator:'.', decimalSeparator: ',', symbolOnLeft: false })}}</td>
                                        <td>{{ props.item.estado }}</td>
                                        <td v-if = "props.item.rfid_id == 2">{{ props.item.rfid+" O:"+props.item.origen+"/D:"+props.item.destino }}</td>
                                        <td v-else>{{ props.item.rfid }}</td>
                                        <td class="justify-center layout px-0">

                                                    <v-btn
                                                        small
                                                        icon
                                                        @click="goProducto(props.item)"
                                                    >
                                                        <v-icon color="blue">local_offer</v-icon>
                                                    </v-btn>


                                            <v-tooltip bottom>
                                                <template v-slot:activator="{ on }">
                                                    <v-btn
                                                        small
                                                        v-on="on"
                                                        icon
                                                        @click="openDialog(props.item)"
                                                    >
                                                        <v-icon color="orange">cancel</v-icon>
                                                    </v-btn>
                                                </template>
                                                <span>Eliminar de recuento</span>
                                            </v-tooltip>
                                            <v-tooltip bottom v-if="!(props.item.rfid_id == 2 || props.item.rfid_id == 3 || (props.item.rfid_id == 4 && props.item.deleted_at == null))">
                                                <template v-slot:activator="{ on }">
                                                    <v-btn

                                                        small
                                                        v-on="on"
                                                        icon
                                                        @click="update(props.item)"
                                                    >

                                                        <v-icon color="red">location_off</v-icon>
                                                    </v-btn>
                                                </template>
                                                <span>Producto Perdido</span>
                                            </v-tooltip>
                                            <v-tooltip bottom v-else>
                                                <template v-slot:activator="{ on }">
                                                    <v-btn

                                                        small
                                                        v-on="on"
                                                        icon
                                                        @click="update(props.item)"
                                                    >
                                                        <v-icon color="green">location_on</v-icon>
                                                    </v-btn>
                                                </template>
                                                <span>Producto localizado</span>
                                            </v-tooltip>
                                            <v-tooltip bottom>
                                                <template v-slot:activator="{ on }">
                                                    <v-btn
                                                        small
                                                        v-on="on"
                                                        icon
                                                        @click="bajaProducto(props.item)"
                                                    >
                                                        <v-icon color="red darken-4">delete</v-icon>
                                                    </v-btn>
                                                </template>
                                                <span>Baja Producto</span>
                                            </v-tooltip>
                                            <v-tooltip bottom v-if="props.item.rfid_id == 2">
                                                <template v-slot:activator="{ on }">
                                                    <v-btn
                                                        small
                                                        v-on="on"
                                                        icon
                                                        @click="reubicarProducto(props.item)"
                                                    >
                                                        <v-icon color="green darken-4">where_to_vote</v-icon>
                                                    </v-btn>
                                                </template>
                                                <span>Reubicar Producto</span>
                                            </v-tooltip>
                                        </td>
                                    </template>
                                    <template slot="pageText" slot-scope="props">
                                        Registros {{ props.pageStart }} - {{ props.pageStop }} de {{ props.itemsLength }}
                                    </template>
                                </v-data-table>
                            </v-flex>
                        </v-layout>
                        <!-- <v-layout row wrap>
                                <v-data-iterator
                                    :items="items"
                                    content-tag="v-layout"
                                    hide-actions
                                    row
                                    wrap
                                    >
                                </v-data-iterator>
                                <v-card>
                                    <v-card-title class="subheading font-weight-bold">{{ props.item.name }}</v-card-title>

                                    <v-divider></v-divider>

                                    <v-list dense>
                                        <v-list-tile>
                                            <v-list-tile-content>Calories:</v-list-tile-content>
                                            <v-list-tile-content class="align-end">{{ props.item.calories }}</v-list-tile-content>
                                        </v-list-tile>
                                    </v-list>
                                </v-card>
                        </v-layout> -->
                    </v-container>
                </v-card>
           </div>
        <div v-if="status==true">
            <v-card>
                        <v-layout row wrap>
                            <v-flex sm4></v-flex>
                            <v-flex sm4>
                                <table class="v-datatable v-table theme--light">
                                    <thead>
                                        <tr>
                                            <th>Concepto</th>
                                            <th>Registros</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in estados_recuento" :key="index">
                                            <td class="text-xs-left">{{item.nombre}}</td>
                                            <td class="text-xs-center">{{ item.registros | currency('', 0, { thousandsSeparator:'.', decimalSeparator: ',', symbolOnLeft: false })}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </v-flex>
                        </v-layout>
                        <v-layout row wrap>
                            <v-flex sm4></v-flex>
                            <v-flex sm3>
                            </v-flex>
                            <v-flex sm1></v-flex>
                            <v-flex sm3>
                                <div class="text-xs-center">
                                    <v-btn @click="detalle"  round flat  block  color="primary">
                                        Detalle Recuento
                                    </v-btn>
                                </div>
                            </v-flex>
                        </v-layout>
                    </v-card>
        </div>
    </div>
</template>
<script>
import {mapGetters} from 'vuex'
import {mapActions} from "vuex";
import moment from 'moment'
import MyDialog from '@/components/shared/MyDialog'
import Loading from '@/components/shared/Loading'
import MenuOpe from './MenuOpe'
import FiltroRec from './FiltroRec'
  export default {
    components: {
        'my-dialog': MyDialog,
        'menu-ope': MenuOpe,
        'loading': Loading,
        'filtro-rec': FiltroRec
    },
    data () {
      return {
        titulo:"Recuentos",
        search:"",
        paginaActual:{},
        pagination:{
            model: "recuentos",
            descending: false,
            page: 1,
            rowsPerPage: 10,
            sortBy: "id",
            search: ""
        },
        headers: [
            {
            text: 'Referencia',
            align: 'left',
            value: 'referencia'
            },
            {
            text: 'Producto',
            align: 'left',
            value: 'nombre'
            },
            {
            text: 'Peso',
            align: 'left',
            value: 'peso_gr'
            },
            {
            text: 'P. Coste',
            align: 'left',
            value: 'precio_coste'
            },
            {
            text: 'Estado',
            align: 'left',
            value: 'estado'
            },
            {
            text: 'Situaci??n',
            align: 'left',
            value: 'rfid'
            },
            {
            text: 'Acciones',
            align: 'Center',
            value: ''
            }
        ],
        filtro: false,
        item_destroy: {},
        items:[],
        status: false,
		registros: false,
        dialog: false,
        show_loading: true,
        editedIndex: 0,
        url:"/mto/recuentos",
        estados_recuento:[],
        status: false
      }
    },
    beforeMount(){

        if (this.getLineasIndex.length > 0)
            if (this.getPagination.model == (this.pagination.model+this.empresaActiva))
                this.items = this.getLineasIndex;
    },
    mounted()
    {

        this.pagination.model="recuentos"+this.empresaActiva;

        if (this.getPagination.model == this.pagination.model)
            this.updatePosPagina(this.getPagination);
        else
            this.unsetPagination();

        if (this.getLineasIndex.length == 0)
            axios.get(this.url)
                .then(res => {
                    this.items = res.data;


                })
                .catch(err =>{
                    this.$toast.error(err.response.data.message);
                    this.$router.push({ name: 'dash' })
                })
                .finally(()=> {

                    this.show_loading = false;
                    this.registros = true;
                });
        else{
            this.registros = true;
            this.show_loading = false;
        }


    },
    computed: {
        ...mapGetters([
            'empresaActiva',
            'getPagination',
            'getLineasIndex',
            'hasExcel'
        ]),
    },
    methods:{
        ...mapActions([
            'setPagination',
            'unsetPagination',
            'setResult'
        ]),
        updateEventPagina(obj){

            this.paginaActual = obj;

        },
        updatePosPagina(pag){

            this.pagination.page = pag.page;
            this.pagination.descending = pag.descending;
            this.pagination.rowsPerPage= pag.rowsPerPage;
            this.pagination.sortBy = pag.sortBy;

        },
        goProducto(item) {

            this.setPagination(this.paginaActual);
            this.setResult(this.items);

            if (item.deleted_at == null && item.empresa_id == item.origen && item.rfid_id != 2)
                this.$router.push({ name: 'producto.edit', params: { id: item.producto_id } })
            else
                this.$router.push({ name: 'producto.show', params: { id: item.producto_id } })
        },
        update(item) {

            this.editedIndex = this.items.indexOf(item)
            //this.editedItem = Object.assign({}, item)

                    axios.put(this.url+"/"+item.recuento_id, {rfid_id : item.rfid_id})
                        .then(res => {


                            item.rfid = res.data.rfid;
                            item.rfid_id = res.data.rfid_id;
                            Object.assign(this.items[this.editedIndex], item)

                            this.$toast.success(res.data.message);
                            //this.loading = false;

                        })
                        .catch(err => {
                            this.$toast.error(err.response.data.message);
                        })
                        .finally(()=> {
                            this.show_loading = false;
                        });

        },
        openDialog (item){

            this.dialog = true;
            this.item_destroy = item;
        },
        destroyReg () {
            this.dialog = false;

            axios.post(this.url+'/'+this.item_destroy.recuento_id,{_method: 'delete'})
                .then(response => {

                    const index = this.items.indexOf(this.item_destroy)
                    //this.items[index]=this.item_destroy;
                    this.items.splice(index, 1)

                    this.$toast.success(response.data.msg);
                })
            .catch(err => {
                this.status = true;
                //console.log(err);
                var msg = err.response.data.message;
                this.$toast.error(msg);

            });

        },
        bajaProducto (item) {

            this.editedIndex = this.items.indexOf(item)

            axios.post('mto/productos/'+item.producto_id,{_method: 'delete'})
                .then(response => {

                    item.deleted_at = response.data.producto.deleted_at;
                    Object.assign(this.items[this.editedIndex], item)
                    this.$toast.success(response.data.msg);
                })
            .catch(err => {
                this.status = true;
                //console.log(err);
                var msg = err.response.data.message;
                this.$toast.error(msg);

            });

        },
        reubicarProducto (item) {

            this.editedIndex = this.items.indexOf(item)

            axios.put('mto/recuentos/'+item.recuento_id+'/reubicar')
                .then(res => {

                    item.rfid = res.data.rfid;
                    item.rfid_id = res.data.rfid_id;
                    Object.assign(this.items[this.editedIndex], item)


                })
            .catch(err => {
                this.status = true;
                //console.log(err);
                var msg = err.response.data.message;
                this.$toast.error(msg);

            });

        },
        goEstados(){

            if (this.estados_recuento.length > 0){
                this.status = false;
                this.estados_recuento = [];
            }

            this.show_loading = true;

            axios.get('/rfid/status')
                .then(res => {
                    this.estados_recuento = res.data;
                })
            .catch(err => {
                this.status = true;
                var msg = err.response.data.message;
                this.$toast.error(msg);

            })
            .finally(()=> {
                if (this.estados_recuento.length > 0)
                    this.status = true;
                this.show_loading = false;
            });

        },
        goBajaPerdidas(){

            if (confirm('??Dar de baja productos con estado referencias perdidas?')){
                this.show_loading = true;

                axios.post('/rfid/baja')
                    .then(res => {
                        this.$toast.success('Dadas de baja!');
                       // this.estados_recuento = res.data;
                    })
                .catch(err => {
                    this.status = true;
                    var msg = err.response.data.message;
                    this.$toast.error(msg);

                })
                .finally(()=> {
                    this.status = false;
                    this.show_loading = false;
                });
            }
        },
        goRestaurar(){

            if (confirm('??Restaurar productos de baja y en recuento?')){
                this.show_loading = true;

                axios.post('/rfid/restaurar')
                    .then(res => {
                        this.$toast.success('Restauradas!');
                      //  this.estados_recuento = res.data;
                    })
                .catch(err => {
                    this.status = true;
                    var msg = err.response.data.message;
                    this.$toast.error(msg);

                })
                .finally(()=> {
                    this.status = false;
                    this.show_loading = false;
                });
            }
        },
        detalle(){
            this.status = false;
        },
        goExcel(){

            this.show_loading = true;
            axios({
                url: "/mto/recuentos/excel",
                method: 'POST',
                responseType: 'blob', // important
                data:{ data: this.items }
                })
            .then(response => {

                let blob = new Blob([response.data])
                let link = document.createElement('a')
                link.href = window.URL.createObjectURL(blob)

                link.download = "Recuento."+new Date().getFullYear()+(new Date().getMonth()+1)+(new Date().getDate())+'.xlsx';

                document.body.appendChild(link);
                link.click()
                document.body.removeChild(link);

                this.$toast.success('Download Ok! '+link.download);

                this.show_loading = false;

            })
            .catch(err => {
                this.$toast.error(err.response.data.message);
                this.show_loading = false;
            });
        },
    }
  }
</script>
<style scoped>
.tachado{
    text-decoration: line-through
}
</style>
