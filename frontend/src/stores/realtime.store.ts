import { defineStore } from 'pinia'
import { ref } from 'vue'
import { connectSocket, disconnectSocket } from '@/services/socket.service'
import type { Socket } from 'socket.io-client'

export const useRealtimeStore = defineStore('realtime', () => {
  const connected = ref(false)
  const socket = ref<Socket | null>(null)
  const productionUpdates = ref<any[]>([])
  const alerts = ref<any[]>([])

  function connect(orgSlug: string, token: string) {
    const s = connectSocket(orgSlug, token)
    socket.value = s

    s.on('connect', () => { connected.value = true })
    s.on('disconnect', () => { connected.value = false })

    s.on('production.entry.created', (data: any) => {
      productionUpdates.value.unshift(data)
      if (productionUpdates.value.length > 50) productionUpdates.value.pop()
    })

    s.on('alert.low_stock', (data: any) => {
      alerts.value.unshift(data)
    })
  }

  function disconnect() {
    disconnectSocket()
    connected.value = false
    socket.value = null
  }

  return { connected, socket, productionUpdates, alerts, connect, disconnect }
})
