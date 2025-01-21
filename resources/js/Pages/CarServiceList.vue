<template>
  <div class="container mx-auto">
    <h2 class="my-5"><strong>Car service dashboard</strong></h2>
    <DataTable
      :value="clients"
      :loading="loading"
      tableStyle="min-width: 50rem"
    >
      <Column field="id" header="Client ID" style="width: 25%">
        <template #body="slotProps"
          ><strong>{{ slotProps.data.id }}</strong></template
        >
      </Column>
      <Column field="name" header="Client name" style="width: 25%">
        <template #body="slotProps">
          <Button :label="slotProps.data.name" icon="pi pi-user" />
        </template>
        <Button label="Profile" icon="pi pi-user" />
      </Column>
      <Column field="idcard" header="Client id card number" style="width: 25%">
        <template #body="slotProps">
          <Tag severity="info" :value="slotProps.data.idcard"></Tag>
        </template>
      </Column>
    </DataTable>
    <Paginator
      :rows="limit"
      :totalRecords="total"
      :rowsPerPageOptions="[10, 25, 50]"
      @page="onPageChange"
    >
    </Paginator>
  </div>
</template>
<script setup>
import axios from "axios";
import { Button, Column, DataTable, Paginator, Tag } from "primevue";
import { onMounted, ref } from "vue";

const clients = ref([]);
const limit = ref(10);
const total = ref(0);
const currentPage = ref(1);
const loading = ref(false);

const onPageChange = ({ page, rows }) => {
  currentPage.value = page + 1;
  limit.value = rows;
  getClients();
};

const getClients = async () => {
  loading.value = true;
  await axios
    .get(`/api/clients?limit=${limit.value}&page=${currentPage.value}`)
    .then((response) => {
      console.log(response);
      clients.value = response.data.data;
      currentPage.value = response.data.current_page;
      total.value = response.data.total;
      loading.value = false;
    })
    .catch((error) => {
      loading.value = false;
      console.error("Hiba történt az adatok lekérése során:", error);
    });
};

onMounted(() => {
  getClients();
});
</script>
