export interface InventoryItem {
  id: string
  product_id: string
  warehouse_id: string
  batch_number: string | null
  lot_number: string | null
  quantity_on_hand: number
  quantity_reserved: number
  quantity_available: number
  manufactured_date: string | null
  expiry_date: string | null
  unit_cost: number | null
  location_code: string | null
  product?: import('./product.types').Product
  warehouse?: Warehouse
}

export interface Warehouse {
  id: string
  name: string
  code: string
  type: 'raw_material' | 'work_in_progress' | 'finished_goods' | 'general'
  address: string | null
  is_active: boolean
}

export interface Vendor {
  id: string
  name: string
  code: string
  contact_person: string | null
  email: string | null
  phone: string | null
  rating: number
  is_active: boolean
}

export interface GrnHeader {
  id: string
  grn_number: string
  vendor_id: string
  warehouse_id: string
  received_date: string
  status: 'draft' | 'received' | 'quality_check' | 'approved' | 'rejected'
  total_amount: number
  notes: string | null
  vendor?: Vendor
  warehouse?: Warehouse
  items?: GrnItem[]
}

export interface GrnItem {
  id: string
  grn_header_id: string
  product_id: string
  quantity_ordered: number
  quantity_received: number
  unit_price: number
  batch_number: string | null
  expiry_date: string | null
  qc_status: 'pending' | 'passed' | 'failed'
  product?: import('./product.types').Product
}

export interface InventoryTransaction {
  id: string
  transaction_number: string
  product_id: string
  warehouse_id: string
  transaction_type: string
  quantity: number
  transaction_date: string
  notes: string | null
}
