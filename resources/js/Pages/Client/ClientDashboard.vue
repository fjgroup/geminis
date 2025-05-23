<template>
    <div>
        <h1>Panel del Cliente</h1>

        <!-- Tabla para mostrar servicios -->
        <table>
            <thead>
                <tr>
                    <th>Nombre del producto</th>
                    <th>Dominio</th>
                    <th>Próxima Fecha de Vencimiento</th>
                    <th>Estado</th>
                    <th>Precio</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="service in services" :key="service.id">
                    <td>{{ service.product_name }}</td>
                    <td>{{ service.domain }}</td>
                    <td>{{ service.next_due_date }}</td>
                    <td>{{ service.status }}</td>
                    <td>{{ service.price }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
    services: {
        type: Array,
        default: () => [],
    },
});
</script>

<style scoped>
/* Añadir estilos básicos si es necesario */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
th, td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}
th {
    background-color: #f2f2f2;
}
</style>
