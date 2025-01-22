<template>
  <div class="container mx-auto mb-5">
    <h1 class="my-10"><strong>Car service logs</strong></h1>
    <ServiceFilter @clearFilter="onClearFilter" @onFilter="onFilter" />
    <template v-if="resultError">
      <Message severity="error" class="w-full mt-5 mb-5">{{
        resultError
      }}</Message>
    </template>
    <template v-if="clients.data">
      <DataTable
        class="clients-table"
        :value="clients.data"
        :loading="loading"
        tableStyle="min-width: 50rem"
        dataKey="id"
        v-model:expandedRows="expandedCarRows"
      >
        <Column field="id" header="Client ID">
          <template #body="clientProps"
            ><strong>{{ clientProps.data.id }}</strong></template
          >
        </Column>
        <Column field="name" header="Client name">
          <template #body="clientProps">
            <Button
              :label="clientProps.data.name"
              icon="pi pi-user"
              severity="secondary"
              @click="onExpandCarRows(clientProps.data.id)"
            />
          </template>
        </Column>
        <Column field="idcard" header="Client id card">
          <template #body="clientProps">
            <Tag severity="info" :value="clientProps.data.idcard"></Tag>
          </template>
        </Column>
        <Column
          v-if="clients.carCount"
          field="carCount"
          header="No. of cars"
          style="width: 20%"
        >
          <template #body>
            {{ clients.carCount }}
          </template></Column
        >
        <Column
          v-if="clients.serviceLogCount"
          field="serviceLogCount"
          header="No. of service logs"
          style="width: 20%"
        >
          <template #body>
            {{ clients.serviceLogCount }}
          </template></Column
        >
        <template #expansion="carProps">
          <template v-if="!carsLoading">
            <Message
              v-if="!carProps.data?.cars?.length"
              severity="secondary"
              class="w-full mt-5 mb-5"
              >Client has not any cars</Message
            >
            <Card v-else>
              <template #title>
                <small
                  ><strong>{{ carProps.data.name }}</strong> cars</small
                ></template
              >
              <template #content>
                <DataTable
                  class="small-datatable"
                  :value="carProps.data?.cars"
                  size="small"
                  dataKey="car_id"
                  v-model:expandedRows="expandedServiceRows"
                  :loading="carsLoading"
                >
                  <Column field="car_id" header="Car id">
                    <template #body="carProps">
                      <Button
                        icon="pi pi-car"
                        severity="secondary"
                        :label="'Car ' + carProps.data.car_id"
                        @click="
                          onExpandServiceRows({
                            clientId: carProps.data.client_id,
                            carId: carProps.data.car_id,
                          })
                        "
                      ></Button>
                    </template>
                  </Column>
                  <Column field="type" header="Car type"></Column>
                  <Column field="registered" header="Registered at"></Column>
                  <Column field="ownbrand" header="Own brand">
                    <template #body="carProps">
                      <Tag
                        :severity="
                          carProps.data.ownbrand ? 'info' : 'secondary'
                        "
                        :value="carProps.data.ownbrand ? 'Yes' : 'No'"
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
                  <template #expansion="serviceProps">
                    <template v-if="!servicesLoading">
                      <Card class="small-datatable">
                        <template #title>
                          <strong
                            >Service log list of Car no.
                            {{ serviceProps.data.car_id }}</strong
                          >
                        </template>
                        <template #content>
                          <DataTable :value="serviceList">
                            <Column
                              header="Log number"
                              field="lognumber"
                            ></Column>
                            <Column header="Event name" field="event"></Column>
                            <Column
                              header="Event date"
                              field="eventtime"
                            ></Column>
                          </DataTable>
                        </template>
                      </Card> </template
                  ></template>
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
const serviceList = ref([]);

const expandedCarRows = ref({});
const expandedServiceRows = ref({});

const onExpandServiceRows = async (options) => {
  const { clientId, carId } = options;
  serviceList.value = [];

  if (carId in expandedServiceRows.value) {
    expandedServiceRows.value = {};
  } else {
    expandedServiceRows.value = {};
    expandedServiceRows.value[carId] = true;
    expandedServiceRows.value = { ...expandedServiceRows.value };
    serviceList.value = await getServices({ clientId, carId });
  }
};

const eventTime = (eventTime, registeredTime) => {
  return eventTime === null ? registeredTime : eventTime;
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
  expandedServiceRows.value = {};
  if (id in expandedCarRows.value) {
    expandedCarRows.value = {};
  } else {
    expandedCarRows.value = {};
    expandedCarRows.value[id] = true;
    expandedCarRows.value = { ...expandedCarRows.value };
    await populateClientCars(id);
  }
};

const populateClientCars = async (clientId) => {
  const clientCars = await getCarsByClientId(clientId);

  clients.value.data = clients.value.data.map((client) => {
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
      clients.value = response.data?.data.length ? response.data : null;
      currentPage.value = 0;
      total.value = 0;
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
      clients.value = response.data;
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
