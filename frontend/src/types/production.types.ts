export interface ProductionPlan {
  id: string
  plan_number: string
  product_id: string
  production_line_id: string
  planned_quantity: number
  actual_quantity: number
  planned_start_date: string
  planned_end_date: string
  actual_start_date: string | null
  actual_end_date: string | null
  status: 'draft' | 'approved' | 'in_progress' | 'completed' | 'cancelled' | 'on_hold'
  priority: 'low' | 'medium' | 'high' | 'urgent'
  batch_number: string | null
  notes: string | null
  product?: import('./product.types').Product
  productionLine?: ProductionLine
}

export interface ProductionLine {
  id: string
  name: string
  code: string
  status: 'active' | 'maintenance' | 'idle' | 'inactive'
  capacity_per_hour: number | null
}

export interface Machine {
  id: string
  name: string
  code: string
  production_line_id: string | null
  machine_type: string | null
  status: 'running' | 'idle' | 'maintenance' | 'breakdown' | 'retired'
  last_maintenance_at: string | null
  next_maintenance_at: string | null
}

export interface ProductionEntry {
  id: string
  production_plan_id: string
  shift_id: string
  machine_id: string | null
  operator_id: string
  entry_date: string
  start_time: string
  end_time: string | null
  planned_quantity: number
  produced_quantity: number
  rejected_quantity: number
  rework_quantity: number
  downtime_minutes: number
  downtime_reason: string | null
  oee_availability: number | null
  oee_performance: number | null
  oee_quality: number | null
  oee_score: number | null
  plan?: ProductionPlan
}

export interface OeeSummary {
  avg_oee: number
  avg_availability: number
  avg_performance: number
  avg_quality: number
  total_produced: number
  total_rejected: number
  total_downtime: number
}
