export interface User {
  id: number;
  name: string;
  email: string;
  role: string;
}

export interface AuthResponse {
  access_token: string;
  token_type: string;
  expires_in: number;
  user: User;
}

export interface IpAddress {
  id: number;
  ip_address: string;
  label: string;
  comment: string | null;
  created_by: number;
  created_at: string;
  updated_at: string;
  creator?: {
    id: number;
    name: string;
    email: string;
  };
}

export interface PaginationMeta {
  current_page: number;
  from: number;
  last_page: number;
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
  path: string;
  per_page: number;
  to: number;
  total: number;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: PaginationMeta;
  links: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };
}

export interface AuditLog {
  id: number;
  user_id: number;
  action: string;
  entity_type: string;
  entity_id: number | null;
  old_values: any | null;
  new_values: any | null;
  session_id: string;
  ip_address: string;
  user_agent: string;
  created_at: string;
  updated_at: string;
  user: {
    id: number;
    name: string;
    email: string;
  };
}