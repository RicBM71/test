<template>
    <v-form>
        <v-container>
            <v-layout row wrap>
                <v-flex sm2>
                    <v-text-field
                        v-model="reg.referencia"
                        v-validate="'min:2|max:20'"
                        :error-messages="errors.collect('referencia')"
                        label="ID/Referencia"
                        hint=".id ó referencia"
                        data-vv-name="referencia"
                        data-vv-as="referencia"
                        required
                        v-on:keyup.enter="submit"
                    >
                    </v-text-field>
                </v-flex>
                <v-flex sm2>
                    <v-text-field
                        v-model="reg.ref_pol"
                        v-validate="'min:2|max:20'"
                        :error-messages="errors.collect('ref_pol')"
                        label="Ref. Policía"
                        data-vv-name="ref_pol"
                        data-vv-as="Ref. policía"
                        v-on:keyup.enter="submit"
                    >
                    </v-text-field>
                </v-flex>
                <v-flex sm3>
                    <v-select
                        v-model="reg.clase_id"
                        data-vv-name="clase_id"
                        data-vv-as="clase"
                        :error-messages="errors.collect('clase_id')"
                        :items="clases"
                        label="Clase"
                        required
                    ></v-select>
                </v-flex>
                <v-flex sm1>
                    <v-select
                        v-model="reg.quilates"
                        :items="quilates"
                        label="Quilates"
                        v-validate="'numeric'"
                        :error-messages="errors.collect('quilates')"
                        data-vv-name="quilates"
                        data-vv-as="quilates"
                    ></v-select>
                </v-flex>
                <v-flex sm2>
                    <v-select
                        v-model="reg.estado_id"
                        v-validate="'numeric'"
                        data-vv-name="estado_id"
                        data-vv-as="estado"
                        :error-messages="errors.collect('estado_id')"
                        :items="estados"
                        label="Estado"
                    ></v-select>
                </v-flex>
                <v-flex sm2>
                    <v-text-field
                        v-model="reg.precio"
                        :error-messages="errors.collect('precio')"
                        label="Precio/Peso"
                        data-vv-name="precio"
                        data-vv-as="importe"
                        hint=":PVP ó =Coste ó Peso"
                        v-on:keyup.enter="submit"
                    >
                    </v-text-field>
                </v-flex>
            </v-layout>
            <v-layout row wrap>
                <v-flex sm3>
                    <v-text-field
                        v-model="reg.notas"
                        v-validate="'max:20'"
                        :error-messages="errors.collect('notas')"
                        label="Nombre/notas"
                        data-vv-name="notas"
                        data-vv-as="notas"
                        hint="nombre producto, =nombre int. ó :notas"
                        v-on:keyup.enter="submit"
                    >
                    </v-text-field>
                </v-flex>
                <v-flex sm2>
                    <v-text-field
                        v-model="reg.caracteristicas"
                        v-validate="'max:20'"
                        :error-messages="errors.collect('caracteristicas')"
                        label="Características"
                        data-vv-name="caracteristicas"
                        data-vv-as="caracteristicas"
                        hint="Talla, color, pureza, quilates - Relojes: S/N"
                        v-on:keyup.enter="submit"
                    >
                    </v-text-field>
                </v-flex>
                <v-flex sm1>
                    <v-menu
                        v-model="menu_d"
                        :close-on-content-click="false"
                        :nudge-right="40"
                        lazy
                        transition="scale-transition"
                        offset-y
                        full-width
                        min-width="290px"
                    >
                        <v-text-field
                            slot="activator"
                            :value="computedFechaD"
                            label="Desde"
                            append-icon="event"
                            v-validate="'date_format:dd/MM/yyyy'"
                            data-vv-name="fecha_d"
                            :error-messages="errors.collect('fecha_d')"
                            data-vv-as="Desde"
                            readonly
                        ></v-text-field>
                        <v-date-picker
                            v-model="reg.fecha_d"
                            no-title
                            locale="es"
                            first-day-of-week="1"
                            @input="menu_d = false"
                        ></v-date-picker>
                    </v-menu>
                </v-flex>
                <v-flex sm1>
                    <v-menu
                        v-model="menu_h"
                        :close-on-content-click="false"
                        :nudge-right="40"
                        lazy
                        transition="scale-transition"
                        offset-y
                        full-width
                        min-width="290px"
                    >
                        <v-text-field
                            slot="activator"
                            :value="computedFechaH"
                            label="Hasta"
                            append-icon="event"
                            v-validate="'date_format:dd/MM/yyyy'"
                            data-vv-name="fecha_h"
                            :error-messages="errors.collect('fecha_h')"
                            data-vv-as="Hasta"
                            readonly
                        ></v-text-field>
                        <v-date-picker
                            v-model="reg.fecha_h"
                            no-title
                            locale="es"
                            first-day-of-week="1"
                            @input="menu_h = false"
                        ></v-date-picker>
                    </v-menu>
                </v-flex>
                <v-flex sm1>
                    <v-select
                        v-model="reg.tipo_fecha"
                        v-validate="'required'"
                        data-vv-name="tipo_fecha"
                        data-vv-as="fecha"
                        :error-messages="errors.collect('tipo_fecha')"
                        :items="fechas"
                        label="Tipo Fecha"
                        required
                        @change="changeTipoFecha"
                    ></v-select>
                </v-flex>
                <v-flex sm1>
                    <v-select
                        v-model="reg.alta"
                        v-validate="'required'"
                        data-vv-name="alta"
                        data-vv-as="alta"
                        :error-messages="errors.collect('alta')"
                        :items="activos"
                        label="Activos"
                    ></v-select>
                </v-flex>
                <v-flex sm1>
                    <v-switch
                        label="eCommerce"
                        v-model="reg.online"
                        color="primary"
                    >
                        ></v-switch
                    >
                </v-flex>
                <v-flex sm1>
                    <v-switch
                        v-if="showEmp"
                        label="Deslocalizar"
                        v-model="reg.sinscope"
                        color="primary"
                    >
                        ></v-switch
                    >
                </v-flex>
            </v-layout>
            <v-layout row wrap>
                <v-flex sm2>
                    <v-select
                        v-model="reg.categoria_id"
                        v-validate="'numeric'"
                        data-vv-name="categoria_id"
                        data-vv-as="categoría"
                        :error-messages="errors.collect('categoria_id')"
                        :items="categorias"
                        label="Categoría"
                    ></v-select>
                </v-flex>
                <v-flex sm2>
                    <v-select
                        v-model="reg.marca_id"
                        v-validate="'numeric'"
                        data-vv-name="marca_id"
                        data-vv-as="marca"
                        :error-messages="errors.collect('marca_id')"
                        :items="marcas"
                        label="Marca"
                    ></v-select>
                </v-flex>
                <v-flex sm2 d-flex>
                    <v-select
                        v-show="showEmp"
                        v-model="reg.empresa_id"
                        v-validate="'numeric'"
                        data-vv-name="empresa_id"
                        data-vv-as="empresa"
                        :error-messages="errors.collect('empresa_id')"
                        :items="empresas"
                        label="Origen Pieza"
                    ></v-select>
                </v-flex>

                <v-flex sm2 d-flex>
                    <v-select
                        v-show="showEmp"
                        v-model="reg.destino_empresa_id"
                        v-validate="'numeric'"
                        data-vv-name="destino_empresa_id"
                        data-vv-as="empresa"
                        :error-messages="errors.collect('destino_empresa_id')"
                        :items="empresas"
                        label="Destino Venta"
                    ></v-select>
                </v-flex>
                <v-flex sm1>
                    <v-select
                        v-model="reg.interno"
                        v-validate="'required'"
                        data-vv-name="interno"
                        data-vv-as="procedencia"
                        :error-messages="errors.collect('interno')"
                        :items="internos"
                        label="Procedencia"
                        required
                    ></v-select>
                </v-flex>
                <v-flex sm2>
                    <v-select
                        v-model="reg.cliente_id"
                        v-validate="'alpha_dash'"
                        data-vv-name="cliente_id"
                        data-vv-as="asociado"
                        :error-messages="errors.collect('cliente_id')"
                        :items="asociados"
                        label="Asociado"
                    ></v-select>
                </v-flex>

                <v-flex sm1>
                    <v-btn
                        @click="submit"
                        :loading="loading"
                        round
                        small
                        block
                        color="info"
                    >
                        Filtrar
                    </v-btn>
                </v-flex>
            </v-layout>
            <v-layout row wrap>
                <v-flex sm2>
                    <v-select
                        v-model="reg.etiqueta_id"
                        v-validate="'numeric'"
                        data-vv-name="etiqueta_id"
                        data-vv-as="categoría"
                        :error-messages="errors.collect('etiqueta_id')"
                        :items="etiquetas"
                        label="Etiqueta"
                    ></v-select>
                </v-flex>
                <v-flex sm2>
                    <v-select
                        v-model="reg.almacen_id"
                        v-validate="'numeric'"
                        data-vv-name="almacen_id"
                        data-vv-as="categoría"
                        :error-messages="errors.collect('almacen_id')"
                        :items="ubicaciones"
                        label="Ubicación"
                    ></v-select>
                </v-flex>
            </v-layout>
        </v-container>
    </v-form>
</template>
<script>
import moment from "moment";
import { mapGetters } from "vuex";
export default {
    $_veeValidate: {
        validator: "new"
    },
    props: ["filtro", "arr_reg", "mi_filtro"],
    data() {
        return {
            loading: false,
            result: false,

            clases: [],
            estados: [],
            asociados: [],
            reg: {
                cliente_id: null,
                referencia: "",
                notas: null,
                ref_pol: "",
                precio: "",
                clase_id: null,
                estado_id: null,
                quilates: "",
                online: false,
                alta: "S",
                fecha_d: "", //new Date().toISOString().substr(0, 10),
                fecha_h: "",
                tipo_fecha: "C",
                empresa_id: "",
                destino_empresa_id: "",
                sinscope: false,
                interno: "T",
                marca_id: null,
                categoria_id: null,
                caracteristicas: null,
                etiqueta_id: null,
                almacen_id: null
            },
            internos: [
                { value: "I", text: "Internos" },
                { value: "E", text: "Externos" },
                { value: "T", text: "Todos" }
            ],
            activos: [
                { value: "S", text: "Si" },
                { value: "N", text: "No" },
                { value: "T", text: "Todos" }
            ],
            fechas: [
                { value: "C", text: "Creación" },
                { value: "M", text: "Modificación" },
                { value: "D", text: "Borrado" }
            ],
            menu_d: false,
            menu_h: false,
            quilates: [],
            empresas: [],
            marcas: [],
            categorias: [],
            etiquetas: [],
            ubicaciones: [],
            showEmp: true
        };
    },
    mounted() {
        this.reg.sinscope = this.hasDesLoc;

        axios
            .get("/utilidades/helppro/filtro")
            .then(res => {
                this.clases = res.data.clases;
                this.marcas = res.data.marcas;
                this.categorias = res.data.categorias;
                this.etiquetas = res.data.etiquetas;
                this.asociados = res.data.asociados;
                this.ubicaciones = res.data.ubicaciones;

                this.asociados.push({ value: null, text: "---" });
                this.asociados.push({
                    value: -1,
                    text: "Sin Proveedor Asignado"
                });

                this.ubicaciones.push({ value: null, text: "---" });

                this.clases.push({ value: null, text: "---" });

                this.estados = res.data.estados;

                this.estados.push({ value: null, text: "---" });

                this.quilates = res.data.quilates;
                this.quilates.push({ value: null, text: "---" });
                this.marcas.push({ value: null, text: "-" });
                this.categorias.push({ value: null, text: "-" });
                this.etiquetas.push({ value: null, text: "-" });

                //this.showEmp = res.data.empresas.length > 1;

                this.empresas = res.data.empresas;
                this.empresas.push({ value: null, text: "---" });
            })
            .catch(err => {
                console.log(err);
                this.$toast.error("Error al montar <filtro-pro>");
            });
    },
    computed: {
        ...mapGetters(["hasDesLoc"]),
        computedFechaD() {
            moment.locale("es");
            return this.reg.fecha_d ? moment(this.reg.fecha_d).format("L") : "";
        },
        computedFechaH() {
            moment.locale("es");
            return this.reg.fecha_h ? moment(this.reg.fecha_h).format("L") : "";
        }
    },
    methods: {
        clearDate() {
            this.reg.fecha_d = null;
        },
        changeTipoFecha() {
            this.reg.alta = this.reg.tipo_fecha == "D" ? "N" : "T";
            //console.log(this.reg);
        },
        submit() {
            if (this.loading === false) {
                this.$validator.validateAll().then(result => {
                    if (result) {
                        this.loading = true;
                        axios
                            .post("/mto/productos/filtrar", this.reg)
                            .then(res => {
                                this.$emit("update:mi_filtro", this.reg);
                                this.$emit("update:arr_reg", res.data);

                                if (res.data.length == 0)
                                    this.$toast.warning(
                                        "No se han encontrado referencias"
                                    );
                                else this.$emit("update:filtro", false);

                                this.loading = false;
                            })
                            .catch(err => {
                                if (err.request.status == 422) {
                                    // fallo de validated.
                                    const msg_valid = err.response.data.errors;
                                    for (const prop in msg_valid) {
                                        // this.$toast.error(`${msg_valid[prop]}`);

                                        this.errors.add({
                                            field: prop,
                                            msg: `${msg_valid[prop]}`
                                        });
                                    }
                                } else {
                                    this.$toast.error(
                                        err.response.data.message
                                    );
                                }
                                this.loading = false;
                            });
                    }
                });
            }
        }
    }
};
</script>
