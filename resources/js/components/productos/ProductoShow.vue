<template>
	<div>
        <loading :show_loading="show_loading"></loading>
        <v-card>
            <v-card-title color="indigo">
                <h2 color="indigo">{{titulo}}</h2>
                <v-spacer></v-spacer>
                <v-tooltip bottom>
                    <template v-slot:activator="{ on }">
                        <v-btn
                            v-on="on"
                            color="white"
                            icon
                            @click="goCompra"
                        >
                            <v-icon color="primary">shopping_cart</v-icon>
                        </v-btn>
                    </template>
                        <span>Ir a compra</span>
                </v-tooltip>
                <menu-ope :id="producto.id"></menu-ope>

            </v-card-title>
        </v-card>
        <v-card>
            <v-form>
                 <v-container  v-if="!show_loading">
                    <v-layout row wrap  v-if="producto.deleted_at != null">
                       <v-flex sm2>
                           <h3 class="red--text darken-4">PRODUCTO BORRADO!</h3>
                       </v-flex>
                    </v-layout>
                    <br/>
                    <v-layout row wrap>
                                <v-flex sm2>
                                    <v-text-field
                                        v-model="producto.referencia"
                                        v-validate="'required|alpha_num'"
                                        :error-messages="errors.collect('referencia')"
                                        :label="computedLabelRef"
                                        data-vv-name="referencia"
                                        data-vv-as="referencia"
                                        readonly
                                    >
                                    </v-text-field>
                                </v-flex>
                                <v-flex sm1>
                                    <v-text-field
                                        v-show="producto.estado_id <= 4"
                                        v-model="producto.ref_pol"
                                        v-validate="'required_if:iva_id,2'"
                                        :error-messages="errors.collect('ref_pol')"
                                        label="Ref. Pol."
                                        data-vv-name="ref_pol"
                                        data-vv-as="Ref. Pol"
                                        readonly
                                    >
                                    </v-text-field>
                                </v-flex>
                                <v-flex sm2 d-flex>
                                    <v-select
                                    v-model="producto.clase_id"
                                    v-validate="'required'"
                                    data-vv-name="clase_id"
                                    data-vv-as="clase"
                                    :error-messages="errors.collect('clase_id')"
                                    :items="clases"
                                    readonly
                                    label="Clase"
                                    ></v-select>
                                </v-flex>
                                <v-flex sm1>
                                    <v-select
                                        v-if="producto.clase_id == 1"
                                        v-model="producto.quilates"
                                        :items="quilates"
                                        label="Quilates"
                                        v-validate="'numeric'"
                                        :error-messages="errors.collect('quilates')"
                                        data-vv-name="quilates"
                                        data-vv-as="quilates"
                                        readonly
                                    ></v-select>
                                </v-flex>
                                <v-flex sm2 d-flex>
                                    <v-select
                                        v-model="producto.univen"
                                        v-validate="'required'"
                                        data-vv-name="univen"
                                        data-vv-as="iva"
                                        :error-messages="errors.collect('univen')"
                                        :items="unidades"
                                        readonly
                                        label="Ud. Venta"
                                    ></v-select>
                                </v-flex>
                                <v-flex sm2 d-flex>
                                    <v-select
                                        v-model="producto.estado_id"
                                        v-validate="'required'"
                                        data-vv-name="estado_id"
                                        data-vv-as="estado"
                                        :error-messages="errors.collect('estado_id')"
                                        :items="estados"
                                        readonly
                                        label="Estado"
                                    ></v-select>
                                </v-flex>
                                <v-flex sm1 v-if="producto.online">
                                    <v-text-field
                                        v-model="producto.ecommerce_id"
                                        v-validate="'numeric'"
                                        :error-messages="errors.collect('ecommerce_id')"
                                        label="eCommerce ID"
                                        data-vv-name="ecommerce_id"
                                        data-vv-as="eCommerce"
                                        readonly
                                        v-on:keyup.enter="submit"
                                    >
                                    </v-text-field>
                                </v-flex>
                            </v-layout>
                            <v-layout row wrap>
                                <v-flex sm6>
                                    <v-text-field
                                        v-model="producto.nombre"
                                        v-validate="'required|max:300'"
                                        :error-messages="errors.collect('nombre')"
                                        label="Nombre"
                                        data-vv-name="nombre"
                                        data-vv-as="nombre"
                                        required
                                        readonly
                                    >
                                    </v-text-field>
                                </v-flex>
                                <v-flex sm3>
                                    <v-text-field
                                        v-show="producto.estado_id != 5"
                                        v-model="producto.caracteristicas"
                                        v-validate="'max:100'"
                                        :error-messages="errors.collect('caracteristicas')"
                                        label="Características: Brillantes: Talla, color, pureza, quilates - Relojes: S/N"
                                        data-vv-name="caracteristicas"
                                        data-vv-as="características"
                                        required
                                        v-on:keyup.enter="submit"
                                    >
                                    </v-text-field>
                                </v-flex>
                                <v-flex sm3>
                                    <v-text-field
                                        v-show="producto.estado_id != 5"
                                        v-model="producto.nombre_interno"
                                        v-validate="'max:190'"
                                        :error-messages="errors.collect('nombre_interno')"
                                        label="Nombre Interno"
                                        data-vv-name="nombre_interno"
                                        data-vv-as="nombre interno"
                                        required
                                        v-on:keyup.enter="submit"
                                    >
                                    </v-text-field>
                                </v-flex>
                            </v-layout>
                            <v-layout row wrap v-show="producto.estado_id != 5">
                                <v-flex sm2 d-flex>
                                    <v-select
                                        readonly
                                        v-model="producto.almacen_id"
                                        v-validate="'numeric'"
                                        data-vv-name="almacen_id"
                                        data-vv-as="ubicación"
                                        :error-messages="errors.collect('almacen_id')"
                                        :items="almacenes"
                                        label="Ubicación"
                                        ></v-select>
                                </v-flex>
                                <v-flex sm4 d-flex>
                                    <v-select
                                        :disabled="!computedEditPro || producto.compra_id > 0"
                                        v-model="producto.cliente_id"
                                        v-validate="'numeric'"
                                        data-vv-name="cliente_id"
                                        data-vv-as="proveedor"
                                        :error-messages="errors.collect('cliente_id')"
                                        :items="asociados"
                                        label="Proveedor"
                                        ></v-select>
                                </v-flex>

                                <v-flex sm4 d-flex>
                                    <v-select
                                        readonly
                                        v-model="producto.destino_empresa_id"
                                        v-validate="'required'"
                                        data-vv-name="destino_empresa_id"
                                        data-vv-as="empresa"
                                        :error-messages="errors.collect('destino_empresa_id')"
                                        :items="empresas"
                                        :label="computedLabelDestino"
                                        ></v-select>
                                </v-flex>
                                <v-flex sm2 d-flex>
                                    <v-select
                                        v-model="producto.etiqueta_id"
                                        v-validate="'required'"
                                        data-vv-name="etiqueta_id"
                                        data-vv-as="estado"
                                        :error-messages="errors.collect('etiqueta_id')"
                                        :items="etiquetas"
                                        readonly
                                        label="Etiqueta"
                                    ></v-select>
                                </v-flex>

                            </v-layout>
                            <v-layout row wrap>
                                <v-flex sm2 d-flex>
                                    <v-select
                                        v-model="producto.iva_id"
                                        v-validate="'required'"
                                        data-vv-name="iva_id"
                                        data-vv-as="iva"
                                        :error-messages="errors.collect('iva_id')"
                                        :items="ivas"
                                        readonly
                                        label="IVA"
                                        ></v-select>
                                </v-flex>
                                <v-flex sm1>
                                    <v-text-field
                                        v-model="producto.peso_gr"
                                        v-validate="'required|decimal:2'"
                                        :error-messages="errors.collect('peso_gr')"
                                        label="Ud./Peso Gr."
                                        data-vv-name="peso_gr"
                                        data-vv-as="peso/unidades"
                                        class="inputPrice"
                                        type="number"
                                        readonly
                                        v-on:keyup.enter="submit"
                                    >
                                    </v-text-field>
                                </v-flex>
                                <v-flex sm1>
                                    <v-text-field
                                        v-model="producto.precio_coste"
                                        v-validate="'required|decimal:2'"
                                        :error-messages="errors.collect('precio_coste')"
                                        label="Precio Coste"
                                        data-vv-name="precio_coste"
                                        data-vv-as="coste"
                                        class="inputPrice"
                                        type="number"
                                        readonly
                                    >
                                    </v-text-field>
                                </v-flex>
                                <v-flex sm1>
                                    <v-text-field
                                        v-model="producto.precio_venta"
                                        v-validate="'required|decimal:2|min:1'"
                                        :error-messages="errors.collect('precio_venta')"
                                        label="PVP"
                                        data-vv-name="precio_venta"
                                        data-vv-as="PVP"
                                        class="inputPrice"
                                        type="number"
                                        readonly
                                    >
                                    </v-text-field>
                                </v-flex>
                                <v-flex sm1>
                                    <v-text-field
                                        :value="computedMargen"
                                        label="Márgen"
                                        class="inputPrice"
                                        disabled
                                    >
                                    </v-text-field>
                                </v-flex>
                                <v-flex sm1 v-if="producto.online">
                                    <v-text-field
                                        v-model="producto.precio_ecommerce"
                                        v-validate="'required|decimal:2|min:1'"
                                        :error-messages="errors.collect('precio_ecommerce')"
                                        label="PVP eCommerce"
                                        data-vv-name="precio_ecommerce"
                                        data-vv-as="PVP eCommerce"
                                        class="inputPrice"
                                        type="number"
                                        readonly
                                    >
                                    </v-text-field>
                                </v-flex>
                                <v-flex sm1 v-else></v-flex>
                                <v-flex sm1 v-if="producto.online">
                                    <v-text-field
                                        :value="computedMargenEC"
                                        label="Márgen eCommerce"
                                        class="inputPrice"
                                        disabled
                                    >
                                    </v-text-field>
                                </v-flex>
                                <v-flex sm1 v-else></v-flex>
                                <v-flex sm1></v-flex>
                                <v-flex sm1 v-if="computedStock">
                                    <v-text-field
                                        v-model="producto.stock"
                                        v-validate="'required|min:1'"
                                        :error-messages="errors.collect('stock')"
                                        label="Stock Inicial"
                                        data-vv-name="stock"
                                        data-vv-as="Stock"
                                        type="number"
                                        readonly
                                    >
                                    </v-text-field>
                                </v-flex>
                                <v-flex sm1 v-if="computedStock">
                                    <v-text-field
                                        v-model="this.stock_real"
                                        label="Stock Real"
                                        readonly
                                    >
                                    </v-text-field>
                                </v-flex>
                                <v-flex sm1></v-flex>
                                <v-flex sm1>
                                    <v-switch
                                        v-show="producto.estado_id != 5"
                                        label="eCommerce"
                                        v-model="producto.online"
                                        readonly
                                        color="primary">
                                    ></v-switch>
                                </v-flex>
                            </v-layout>
                            <v-layout row wrap>
                                <v-flex sm2 d-flex>
                                    <v-select
                                        v-show="producto.estado_id != 5"
                                        v-model="producto.garantia_id"
                                        v-validate="'numeric'"
                                        data-vv-name="garantia_id"
                                        data-vv-as="garantia"
                                        :error-messages="errors.collect('garantia_id')"
                                        :items="garantias"
                                        readonly
                                        label="Garantía"
                                    ></v-select>
                                </v-flex>
                                <v-flex sm1 d-flex>
                                    <v-text-field
                                        v-show="producto.estado_id != 5"
                                        v-model="producto.meses_garantia"
                                        v-validate="'numeric|max_value:24'"
                                        :error-messages="errors.collect('meses_garantia')"
                                        label="Meses"
                                        data-vv-name="meses_garantia"
                                        data-vv-as="meses"
                                        readonly
                                    >
                                    </v-text-field>
                                </v-flex>
                                <v-flex sm2 d-flex>
                                    <v-menu
                                        v-show="producto.estado_id != 5"
                                        v-model="menu1"
                                        :close-on-content-click="false"
                                        :nudge-right="40"
                                        lazy
                                        transition="scale-transition"
                                        offset-y
                                        full-width
                                        min-width="290px"
                                        :disabled="computedEditGarantia"
                                    >
                                        <v-text-field
                                            slot="activator"
                                            :value="computedFechaRevision"
                                            label="Fecha Revisión"
                                            append-icon="event"
                                            data-vv-as="Última Revisión"
                                            :error-messages="errors.collect('fecha_ultima_revision')"
                                            readonly
                                            :disabled="computedEditGarantia"
                                            ></v-text-field>
                                        <v-date-picker
                                            v-model="producto.fecha_ultima_revision"
                                            no-title
                                            locale="es"
                                            first-day-of-week=1
                                            @input="menu1 = false"
                                        ></v-date-picker>
                                    </v-menu>
                                </v-flex>
                                <v-flex sm2 d-flex>
                                    <v-select
                                        v-model="producto.categoria_id"
                                        v-validate="'numeric'"
                                        data-vv-name="categoria_id"
                                        data-vv-as="categoria"
                                        :error-messages="errors.collect('categoria_id')"
                                        :items="categorias"
                                        label="Categoría"
                                    ></v-select>
                                </v-flex>
                                <v-flex sm2 d-flex>
                                    <v-select
                                        v-model="producto.marca_id"
                                        v-validate="'numeric'"
                                        data-vv-name="marca_id"
                                        data-vv-as="marca"
                                        :error-messages="errors.collect('marca_id')"
                                        :items="marcas"
                                        label="Marca"
                                    ></v-select>
                                </v-flex>
                            </v-layout>
                            <v-layout row wrap>
                                <v-flex sm6>
                                    <v-textarea
                                        v-model="producto.notas"
                                        v-validate="'max:300'"
                                        :error-messages="errors.collect('notas')"
                                        label="Observaciones"
                                        data-vv-name="notas"
                                        data-vv-as="notas"
                                    >
                                    </v-textarea>
                                </v-flex>
                                <v-flex sm2>
                                    <v-text-field
                                        v-model="producto.username"
                                        label="Usuario"
                                        data-vv-name="username"
                                        readonly
                                        :error="'error'"
                                    >
                                    </v-text-field>
                                </v-flex>
                                <v-flex sm2>
                                    <v-text-field
                                        v-model="computedFModFormat"
                                        label="Modificado"
                                        readonly
                                        :error="'error'"
                                    >
                                    </v-text-field>
                                </v-flex>
                                <v-flex sm2>
                                    <v-text-field
                                        v-model="computedFCreFormat"
                                        label="Creado"
                                        readonly
                                    >
                                    </v-text-field>
                                </v-flex>
                            </v-layout>
                            <v-layout row wrap>
                                <v-textarea
                                    v-show="producto.estado_id != 5"
                                    v-model="producto.descripcion"
                                    :error-messages="errors.collect('descripcion')"
                                    label="Descripción"
                                    data-vv-name="descripcion"
                                    data-vv-as="descripción"
                                    rows="5"
                                >
                                </v-textarea>
                            </v-layout>
                </v-container>
            </v-form>
        </v-card>
	</div>
</template>
<script>
import moment from 'moment'
import Loading from '@/components/shared/Loading'
import MenuOpe from './MenuOpe'
import {mapGetters} from 'vuex';
	export default {
		$_veeValidate: {
      		validator: 'new'
        },
        components: {
            'menu-ope': MenuOpe,
            'loading': Loading
		},
    	data () {
      		return {
                titulo:"Productos",
                producto: {},
                url: "/mto/productos",
                ruta: "producto",
                clases: [],
                estados: [],
                etiquetas: [],
                almacenes: [],
                asociados: [],
                garantias:[],
                quilates:[],
                marcas:[],
                categorias:[],
                ivas:[],
                empresas:[],
                tags:[],
                chips:[ {value:1},{value:2}],

                unidades: [
                    {'value': 'U', 'text': 'Unidades'},
                    {'value': 'G', 'text': 'Gramos'}
                ],


                saldo: 0,
                enviando: false,
                menu1: false,

                show: false,
                show_loading: true,
      		}
        },
        mounted(){

            var id = this.$route.params.id;
            if (id > 0)
                axios.get(this.url+'/'+id)
                    .then(res => {

                        this.empresas = res.data.empresas;
                        this.clases = res.data.clases;
                        this.estados = res.data.estados;
                        this.ivas = res.data.ivas;
                        this.etiquetas = res.data.etiquetas;
                        this.almacenes = res.data.almacenes;
                        this.asociados = res.data.asociados;
                        this.garantias = res.data.garantias;
                        this.quilates  = res.data.quilates;
                        this.marcas = res.data.marcas;
                        this.categorias = res.data.categorias;

                        this.producto = res.data.producto;
                        this.show = true;
                        this.show_loading = false;
                    })
                    .catch(err => {
                        this.$toast.error(err.response.data.message);
                        this.$router.push({ name: this.ruta+'.index'})
                    })
        },
        computed: {
        ...mapGetters([
            ]),
        computedEditEstado(){

               //if (this.isRoot) return false;
               return !this.isRoot;

                return (this.producto.estado_id == 3 || this.producto.estado_id == 4);
            },
            computedLabelRef(){
                return "Referencia/Id "+this.producto.id;
            },
            computedEditGarantia(){

                if (this.hasEdtPro == true) return false;

                return this.producto.estado_id > 3;

            },
            computedStock(){

                return this.show_stock;

                //return (this.producto.estado_id <= 3 && this.producto.stock > 1);

            },
            computedEditStock(){
                if (this.show_stock && this.hasEdtPro)
                    return true;
                return false;
            },
            computedEditCoste(){

                if (this.producto.estado_id == 5 || this.hasEdtFac)
                    return true;

                if (this.producto.estado_id == 3 || this.producto.estado_id == 4)
                    return false;

                //     return this.hasEdtPro;

                const hoy = new Date().toISOString().substr(0, 10);
                if (this.producto.username == this.userName && this.producto.created_at.substr(0, 10) == hoy)
                    return true;
                else
                    return this.hasEdtPro;

            },
            computedEditPro(){

                if (this.producto.estado_id == 5)
                    return true;

                if (this.producto.estado_id == 3 || this.producto.estado_id == 4)
                    return false;

                //     return this.hasEdtPro;

                const hoy = new Date().toISOString().substr(0, 10);
                if (this.producto.username == this.userName && this.producto.created_at.substr(0, 10) == hoy)
                    return true;
                else
                    return this.hasEdtPro;
            },
            computedLabelDestino(){

                if (this.producto.id > 0)
                    return "Destino venta - Procede de "+ this.producto.empresa.nombre.toUpperCase();

                return "";

            },
            computedMargen(){
                return this.getMoneyFormat(this.producto.margen);
            },
            computedMargenEC(){
                return this.getMoneyFormat(this.producto.margenec);
            },
            computedFecha() {
                moment.locale('es');
                return this.producto.fecha ? moment(this.producto.fecha).format('L') : '';
            },
            computedFechaRevision() {
                    moment.locale('es');
                    return this.producto.fecha_ultima_revision ? moment(this.producto.fecha_ultima_revision).format('L') : '';
            },
            computedFModFormat() {
                moment.locale('es');
                return this.producto.updated_at ? moment(this.producto.updated_at).format('DD/MM/YYYY H:mm:ss') : '';
            },
            computedFCreFormat() {
                moment.locale('es');
                return this.producto.created_at ? moment(this.producto.created_at).format('DD/MM/YYYY H:mm') : '';
            }

        },
    	methods:{
            goCompra() {
                this.$router.push({ name: 'compra.close', params: { id: this.producto.compra_id } })
            },
            getMoneyFormat(value){
                return new Intl.NumberFormat("de-DE",{style: "currency", currency: "EUR"}).format(parseFloat(value))
            },
            getDecimalFormat(value){
                return new Intl.NumberFormat("de-DE",{style: "decimal",minimumFractionDigits:2}).format(parseFloat(value))
            },
    }
  }
</script>
<style scoped>

.inputPrice >>> input {
  text-align: center;
  -moz-appearance:textfield;
}

input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    margin: 0;
}

.v-form>.container>.layout>.flex {
    padding: 1px 8px 1px 8px;
}
.v-text-field {
    padding-top: 6px;
    margin-top: 2px;
}


.theme--light.v-input--is-disabled,  .theme--light.v-input--is-disabled input, .theme--light.v-input--is-disabled textarea {
    color: #263238;
}

.v-form>.container>.layout>.flex{
    padding: 0px 8px;
}

.inputPrice >>> input {
  text-align: center;
  -moz-appearance:textfield;
}

input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    margin: 0;
}
</style>
