<template>
  <div class="container mx-auto mb-5">
    <h1 class="my-10"><strong>Car service logs</strong></h1>
    <ServiceFilter @clearFilter="onClearFilter" @onFilter="onFilter" />
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
        v-model:expandedRows="expandedCarRows"
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
              @click="onExpandCarRows(slotProps.data.id)"
            />
          </template>
        </Column>
        <Column field="idcard" header="Client id card">
          <template #body="slotProps">
            <Tag severity="info" :value="slotProps.data.idcard"></Tag>
          </template>
        </Column>
        <template #expansion="slotProps">
          <template v-if="!carsLoading">
            <Message
              v-if="!slotProps.data?.cars?.length"
              severity="secondary"
              class="w-full mt-5 mb-5"
              >Client has not any cars</Message
            >
            <Card v-else>
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
                  <Column field="car_id" header="Car id">
                    <template #body="slotProps">
                      <Button
                        icon="pi pi-car"
                        severity="secondary"
                        :label="'Car ' + slotProps.data.car_id"
                        @click="
                          onExpandRowServices({
                            clientId: slotProps.data.client_id,
                            carId: slotProps.data.car_id,
                          })
                        "
                      ></Button>
                    </template>
                  </Column>
                  <Column field="type" header="Car type"></Column>
                  <Column field="registered" header="Registered at"></Column>
                  <Column field="ownbrand" header="Own brand">
                    <template #body="slotProps">
                      <Tag
                        :severity="
                          slotProps.data.ownbrand ? 'info' : 'secondary'
                        "
                        :value="slotProps.data.ownbrand ? 'Yes' : 'No'"
                      ></Tag>
                    </template>
                  </Column>
                  <Column field="accident" header="No. of accident(s)"></Column>
                  <Column
                    field="latestLog.event"
                    header="Latest event"
                  ></Column>
                  <Column
                    field="latestLog.eventtime"
                    header="Latest event date"
                  ></Column>
                </DataTable>
              </template>
            </Card>
          </template>
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
import ServiceFilter from "@/Components/ServiceFilter.vue";
import axios from "axios";
import {
  Button,
  Card,
  Column,
  DataTable,
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
const servicesLoading = ref(false);

const expandedCarRows = ref({});

const onExpandRowServices = async (options) => {
  const { clientId, carId } = options;
  const servicesResult = await getServices({ clientId, carId });
  console.log(servicesResult);
};

const getServices = async (options) => {
  const { clientId, carId } = options;
  servicesLoading.value = true;
  return await axios
    .get(`/api/carServicesByClient?clientId=${clientId}&carId=${carId}`)
    .then((response) => {
      servicesLoading.value = false;
      return response.data;
    })
    .catch((error) => {
      servicesLoading.value = false;
      console.error("Error during getServices:", error);
      return false;
    });
};

const onExpandCarRows = async (id) => {
  if (expandedCarRows.value[id]) {
    // If the ID exists, remove it
    delete expandedCarRows.value[id];
  } else {
    // If the ID doesn't exist, add it
    expandedCarRows.value[id] = true;
    await populateClientCars(id);
  }

  // Trigger reactivity by creating a new object reference
  expandedCarRows.value = { ...expandedCarRows.value };
};

const populateClientCars = async (clientId) => {
  const clientCars = await getCarsByClientId(clientId);

  clients.value = clients.value.map((client) => {
    if (client.id === clientId) {
      client.cars = clientCars;
    }
    return client;
  });
};

const onPageChange = ({ page, rows }) => {
  expandedCarRows.value = {};
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

const onClearFilter = () => {
  filteredClient.value = null;
  currentPage.value = 1;
  resultError.value = false;
  getClients();
};

const onFilter = (options) => {
  resultError.value = false;
  getFilteredClients(options);
};

const getFilteredClients = async (options) => {
  const { clientName, clientIdCard } = options;
  loading.value = true;

  await axios
    .get(
      `/api/filterClients?clientName=${clientName}&clientIdCard=${clientIdCard}`
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
  expandedCarRows.value = {};
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
