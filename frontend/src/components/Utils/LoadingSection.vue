<template>
    <Modal :modalActive="isFetching">
        <div class="modal-content">
            <span>
                <br><br><br>
                <div class="container">
                    <div class="content-center">
                        <h1>Carregando...<span class="fas fa-spinner fa-pulse"></span></h1>
                    </div>
                </div>
            </span>
        </div>
    </Modal>

</template>

<script>
import Modal from "../Utils/ModalDefault.vue";
import { mapState } from "vuex";

export default {
    components: { Modal },

    computed: {
        ...mapState([
            "isFetching",
        ]),

    },


}
</script>