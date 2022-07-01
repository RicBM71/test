<template>
    <div>
        <my-dialog :dialog.sync="dialog" registro="registro" @destroyReg="destroyReg"></my-dialog>
         <v-dialog v-model="dialog_help" persistent max-width="800">
            <v-card>
                <v-card-title class="headline">Roles y Permisos</v-card-title>
                <v-card-text>
                    <v-list three-line>
                        <v-list-tile-content>
                            <v-list-tile-title>Permisos</v-list-tile-title>
                            <v-list-tile-sub-title>*Borra compras: borrado (hard delete) de una compra, se elimina de la BBDD.</v-list-tile-sub-title>
                            <v-list-tile-sub-title>*Deslocalizar: elimina el filtrad de productos por empresa.</v-list-tile-sub-title>
                            <v-list-tile-sub-title>*Hard Delete: borrado de BBDD de productos.</v-list-tile-sub-title>
                            <v-list-tile-sub-title>*Saltar límite efectivo: desactiva el control de límites.</v-list-tile-sub-title>
                            <v-list-tile-sub-title>*Edita facturas: edición de lineas de productos de albaranes facturados. </v-list-tile-sub-title>
                            <v-list-tile-sub-title>*Edita facturas: edición de lineas de productos de albaranes facturados. </v-list-tile-sub-title>
                            <v-list-tile-sub-title>Cambia ubicación: edición del campo ubicación de compras: banco, tienda, etc.. </v-list-tile-sub-title>
                        </v-list-tile-content>
                    </v-list>
                </v-card-text>
                <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" round flat @click="dialog_help = false">Cerrar</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <v-tooltip bottom>
            <template v-slot:activator="{ on }">
                <v-btn
                    v-show="computedAdd"
                    v-on="on"
                    color="white"
                    icon
                    @click="goCreate"
                >
                    <v-icon color="primary">add</v-icon>
                </v-btn>
            </template>
                <span>Nuevo</span>
        </v-tooltip>
         <v-tooltip bottom>
            <template v-slot:activator="{ on }">
                <v-btn
                    v-show="id > 0"
                    v-on="on"
                    color="white"
                    icon
                    @click="openDialog"
                >
                    <v-icon color="primary">delete</v-icon>
                </v-btn>
            </template>
                <span>Borrar Registro</span>
        </v-tooltip>
        <v-tooltip bottom>
            <template v-slot:activator="{ on }">
                <v-btn
                    v-on="on"
                    color="white"
                    icon
                    @click="goIndex"
                >
                    <v-icon color="primary">list</v-icon>
                </v-btn>
            </template>
            <span>Lista</span>
        </v-tooltip>
        <v-tooltip bottom>
            <template v-slot:activator="{ on }">
                <v-btn
                    v-on="on"
                    color="white"
                    icon
                    @click="dialog_help=true"
                >
                    <v-icon color="primary">help_outline</v-icon>
                </v-btn>
            </template>
            <span>Ayuda</span>
        </v-tooltip>
    </div>
</template>
<script>
import MyDialog from '@/components/shared/MyDialog'
import {mapGetters} from 'vuex';
export default {
    props:{
        id: Number
    },
    components: {
        'my-dialog': MyDialog
    },
    data () {
      return {
          dialog: false,
          dialog_help: false,
      }
    },
    computed: {
        ...mapGetters([
            'isRoot',
            'aislar'
        ]),
        computedAdd(){

            if (this.aislar)
                return this.isRoot;
            else
                return true;
        }

    },
    methods:{
        goCreate(){
            this.$router.push({ name: 'users.create' })
        },
        goIndex(){
            this.$router.push({ name: 'users.index' })
        },
        openDialog (){
            this.dialog = true;
        },
        destroyReg () {
            this.dialog = false;

            axios.post('/admin/users/'+this.id,{_method: 'delete'})
                .then(response => {
                this.$router.push({ name: 'users.index' })
                this.$toast.success('Usuario eliminada!');

            })
            .catch(err => {
                this.status = true;
                var msg = err.response.data.message;
                this.$toast.error(msg);

            });

        },

        goHelp(){
            this.dialog_help = true;
        }

    }
}
</script>
