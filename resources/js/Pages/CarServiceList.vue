<template>
  <div class="container mx-auto mb-5">
    <h1 class="my-10"><strong>Car service logs</strong></h1>
    <Card class="mb-5">
      <template #title>Search client</template>
      <template #content>
        <div class="flex flex-nowrap">
          <InputText
            v-model="filterClientName"
            @keyup.enter="onFilter"
            placeholder="Client name"
            class="flex-initial w-64"
          />
          <InputText
            v-model="filterClientIdCard"
            @keyup.enter="onFilter"
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
          <Button
            v-if="filterClientName || filterClientIdCard"
            label="Clear search"
            severity="danger"
            icon="pi pi-del"
            class="ml-5"
            @click="clearFilter"
          ></Button>
        </div>
        <div class="flex mt-5" v-if="filterErrorMessage">
          <Message
            severity="error"
            size="small"
            icon="pi pi pi-times-circle"
            class="w-full"
            >{{ filterErrorMessage }}</Message
          >
        </div>
      </template>
    </Card>
    <template v-if="resultError">
      <Message severity="error" class="w-full mt-5 mb-5">{{
        resultError
      }}</Message>
    </template>
    <template v-if="filteredClient">
      <DataTable
        class="filtered-client-table"
        :value="filteredClient"
        :loading="loading"
        tableStyle="min-width: 50rem"
      >
        <Column field="client.id" header="Client ID" style="width: 20%">
          <template #body="slotProps"
            ><strong>{{ slotProps.data.client.id }}</strong></template
          >
        </Column>
        <Column field="client.name" header="Client name" style="width: 20%">
          <template #body="slotProps">
            <Button
              :label="slotProps.data.client.name"
              icon="pi pi-user"
              severity="secondary"
            />
          </template>
        </Column>
        <Column
          field="client.idcard"
          header="Client id card"
          style="width: 20%"
        >
          <template #body="slotProps">
            <Tag severity="info" :value="slotProps.data.client.idcard"></Tag>
          </template>
        </Column>
        <Column
          field="carCount"
          header="No. of cars"
          style="width: 20%"
        ></Column>
        <Column
          field="serviceLogCount"
          header="No. of service logs"
          style="width: 20%"
        ></Column>
      </DataTable>
    </template>
    <template v-if="clients.length">
      <DataTable
        class="clients-table"
        :value="clients"
        :loading="loading"
        tableStyle="min-width: 50rem"
        dataKey="id"
        v-model:expandedRows="expandedRows"
      >
        <Column field="id" header="Client ID">
          <template #body="slotProps"
            ><strong>{{ slotProps.data.id }}</strong></template
          >
        </Column>
        <Column field="name" header="Client name">
          <template #body="slotProps">
            <Button
              :label="slotProps.data.name"
              icon="pi pi-user"
              severity="secondary"
              @click="onExpandRow(slotProps.data.id)"
            />
          </template>
        </Column>
        <Column field="idcard" header="Client id card">
          <template #body="slotProps">
            <Tag severity="info" :value="slotProps.data.idcard"></Tag>
          </template>
        </Column>
        <template #expansion="slotProps">
          <Card>
            <template #title>
              <small
                ><strong>{{ slotProps.data.name }}</strong> cars</small
              ></template
            >
            <template #content>
              <DataTable
                class="cars-datatable"
                :value="slotProps.data?.cars"
                size="small"
                :loading="carsLoading"
              >
                <Column field="car_id" header="Car id"></Column>
                <Column field="type" header="Car type"></Column>
                <Column field="registered" header="Registered at"></Column>
                <Column field="ownbrand" header="Own brand"></Column>
                <Column field="accident" header="No accident(s)"></Column>
                <Column field="latestLog.event" header="Latest event"></Column>
                <Column
                  field="latestLog.eventtime"
                  header="Latest event date"
                ></Column>
              </DataTable>
            </template>
          </Card>
        </template>
      </DataTable>
      <Paginator
        :rows="limit"
        :totalRecords="total"
        :rowsPerPageOptions="[10, 25, 50]"
        @page="onPageChange"
      >
      </Paginator>
    </template>
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
  Message,
  Paginator,
  Tag,
} from "primevue";
import { onMounted, ref } from "vue";

const clients = ref([]);
const filteredClient = ref(null);
const limit = ref(10);
const total = ref(0);
const currentPage = ref(1);
const resultError = ref(false);
const loading = ref(false);
const carsLoading = ref(false);

const expandedRows = ref({});

const filterClientName = ref("");
const filterClientIdCard = ref("");

const filterErrorMessage = ref(null);

const onExpandRow = async (id) => {
  if (expandedRows.value[id]) {
    // If the ID exists, remove it
    delete expandedRows.value[id];
  } else {
    // If the ID doesn't exist, add it
    expandedRows.value[id] = true;
    await populateClientCars(id);
  }

  // Trigger reactivity by creating a new object reference
  expandedRows.value = { ...expandedRows.value };
};

const populateClientCars = async (clientId) => {
  const clientCars = await getCarsByClientId(clientId);

  clients.value = clients.value.map((client) => {
    if (client.id === clientId) {
      client.cars = clientCars;
    }
    return client;
  });

  // console.log(clients.value);
  console.log(clients);
};

const onPageChange = ({ page, rows }) => {
  currentPage.value = page + 1;
  limit.value = rows;
  getClients();
};

const getCarsByClientId = async (clientId) => {
  carsLoading.value = true;
  return await axios
    .get(`/api/carsByClient?clientId=${clientId}`)
    .then((response) => {
      carsLoading.value = false;
      return response.data;
    })
    .catch((error) => {
      carsLoading.value = false;
      console.error("Error during getCarsByClientId:", error);
      return false;
    });
};

const clearFilter = () => {
  filterClientName.value = "";
  filterClientIdCard.value = "";
  filteredClient.value = null;
  currentPage.value = 1;
  resultError.value = false;
  filterErrorMessage.value = null;
  getClients();
};

const onFilter = () => {
  resultError.value = false;
  filterErrorMessage.value = null;

  if (filterClientName.value.length && filterClientIdCard.value.length) {
    filterErrorMessage.value =
      "Search only Client name or Client ID card number but not both!";
  }

  if (!filterClientName.value && !filterClientIdCard.value) {
    filterErrorMessage.value =
      "Please provide either a Client name or Client ID card number!";
  }

  if (filterClientIdCard.value && isNaN(filterClientIdCard.value)) {
    filterErrorMessage.value = "Client ID card number must be a numeric value!";
  }

  if (!filterErrorMessage.value) {
    getFilteredClients();
  }
};

const getFilteredClients = async () => {
  loading.value = true;

  await axios
    .get(
      `/api/filterClients?clientName=${filterClientName.value}&clientIdCard=${filterClientIdCard.value}`
    )
    .then((response) => {
      clients.value = [];
      filteredClient.value = response.data?.length ? response.data : null;
      loading.value = false;

      if (response.data?.length === 0) {
        resultError.value = "No client were found matching your criteria.";
      }
    })
    .catch((error) => {
      loading.value = false;
      resultError.value = error?.response?.data?.body?.error?.message;
      console.error("Error during getFilteredClients:", error);
    });
};

const getClients = async () => {
  loading.value = true;
  await axios
    .get(`/api/clients?limit=${limit.value}&page=${currentPage.value}`)
    .then((response) => {
      filteredClient.value = null;
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
<style lang="scss">
@import "../style.scss";
</style>
