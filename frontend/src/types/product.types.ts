export type ProductType = 'raw_material' | 'semi_finished' | 'finished_good' | 'consumable' | 'service'

export interface Product {
  id: string
  name: string
  sku: string
  barcode: string | null
  category_id: string | null
  unit_id: string
  type: ProductType
  description: string | null
  hsn_code: string | null
  tax_rate: number
  cost_price: number
  selling_price: number
  min_stock_level: number
  max_stock_level: number | null
  reorder_point: number
  lead_time_days: number
  is_trackable: boolean
  is_active: boolean
  category?: Category
  unit?: Unit
}

export interface Category {
  id: string
  name: string
  code: string
  parent_id: string | null
  type: string
}

export interface Unit {
  id: string
  name: string
  symbol: string
  type: string
}

export interface BomItem {
  id: string
  parent_product_id: string
  component_product_id: string
  quantity: number
  unit_id: string
  scrap_percentage: number
  sequence: number
  component?: Product
  unit?: Unit
}

export interface ProductFilters {
  search?: string
  type?: ProductType
  category_id?: string
  is_active?: boolean
  page?: number
  per_page?: number
}
