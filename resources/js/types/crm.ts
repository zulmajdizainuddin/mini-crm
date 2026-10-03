export type Company = {
    id: number;
    name: string;
    email: string | null;
    website: string | null;
    logo: string | null;
    logo_url: string | null;
    employees_count?: number;
    created_at: string;
    updated_at: string;
};

export type CompanyOption = Pick<Company, 'id' | 'name'>;

export type Employee = {
    id: number;
    first_name: string;
    last_name: string;
    full_name: string;
    company_id: number | null;
    email: string | null;
    phone: string | null;
    company?: Pick<Company, 'id' | 'name' | 'logo_url'> | null;
    created_at: string;
    updated_at: string;
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
    page?: number | null;
};

/** Shape of Laravel's LengthAwarePaginator when serialised to JSON. */
export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
    prev_page_url: string | null;
    next_page_url: string | null;
};
