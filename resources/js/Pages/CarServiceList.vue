<template>
  <div class="container mx-auto mb-5">
    <h1 class="my-10"><strong>Car service logs</strong></h1>
    <Card class="mb-5">
      <template #title>Filter client</template>
      <template #content>
        <div class="flex flex-nowrap">
          <InputText
            v-model="filterClientName"
            placeholder="Client name"
            class="flex-initial w-64"
          />
          <InputText
            v-model="filterClientIdCard"
            class="mx-5"
            placeholder="Client id card"
          />
          <Button
            label="Search"
            severity="info"
            icon="pi pi-search"
            class="ml-auto"
            @click="onFilter"
          ></Button>
        </div>
      </template>
    </Card>
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
          <Button
            :label="slotProps.data.name"
            icon="pi pi-user"
            severity="secondary"
          />
        </template>
      </Column>
      <Column field="idcard" header="Client id card" style="width: 25%">
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
import {
  Button,
  Card,
  Column,
  DataTable,
  InputText,
  Paginator,
  Tag,
} from "primevue";
import { onMounted, ref } from "vue";

const clients = ref([]);
const limit = ref(10);
const total = ref(0);
const currentPage = ref(1);
const loading = ref(false);

const filterClientName = ref(null);
const filterClientIdCard = ref(null);

const onPageChange = ({ page, rows }) => {
  currentPage.value = page + 1;
  limit.value = rows;
  getClients();
};

const onFilter = () => {
  getFilteredClients();
};

const getFilteredClients = async () => {
  loading.value = true;

  await axios
    .get(
      `/api/filterClients?limit=${limit.value}&page=${currentPage.value}&clientName=${filterClientName.value}&clientIdCard=${filterClientIdCard.value}`
    )
    .then((response) => {
      clients.value = response.data.data;
      currentPage.value = response.data.current_page;
      total.value = response.data.total;
      loading.value = false;
    })
    .catch((error) => {
      loading.value = false;
      console.error("Error during getFilteredClients:", error);
    });
};

const getClients = async () => {
  loading.value = true;
  await axios
    .get(`/api/clients?limit=${limit.value}&page=${currentPage.value}`)
    .then((response) => {
      clients.value = response.data.data;
      currentPage.value = response.data.current_page;
      total.value = response.data.total;
      loading.value = false;
    })
    .catch((error) => {
      loading.value = false;
      console.error("Error during getClients:", error);
    });
};

onMounted(() => {
  getClients();
});
</script>
