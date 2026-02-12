# WORKFLOW
1. Login / Authentication

First page every cashier sees

Purpose:

Identify the cashier

Track shifts and transactions per user

Enforce role-based access

Key elements:

Email / PIN / password login (PIN optional for speed)

Remember me for kiosk mode

Show role (USER, ADMIN, OWNER)

Validate active status (status = ACTIVE)

Flow logic:

User enters credentials

Check users.status == ACTIVE

Redirect to Open Shift page (if no open shift exists)

Otherwise, redirect to POS Dashboard

2. Shift Management (Open / Close Shift)

Every cashier must open a shift before transacting

Page / modal:

Open shift form

Opening Cash (input)

Display current open shifts (if any)

Option to close shift (once done)

Requires counting cash / reconciling with cash_movements and sale_payments

Flow rules:

Cannot process sales without an open shift

Only one open shift per user

Record shift.opened_at and shift.opening_cash

Record user_id in shift

Tip: Lock POS buttons until shift is open

3. POS Dashboard / Sale Page

Main page where sales happen

Components:

Product search / barcode scan

Category filter (drill-down using product_categories)

Product list / quick add buttons

Current sale cart (session or Livewire-managed)

Payment button / tender options

Discounts / promotions (if applicable)

Flow:

Cashier adds items to cart

System calculates subtotal, tax (optional), discounts

Show current total and running inventory check

Prevent adding products if stock insufficient (query inventory_movements ledger)

Allow editing quantity, removing items

Backend logic:

Items are added in session or temporary sale table

Stock is not decremented until sale is completed

Show real-time stock for audit & shrink prevention

4. Checkout / Payment

This is the moment of truth

Options:

Full payment

Cash, card, mobile money

Split payments

Partial payment (rare for POS, optional)

Flow:

Cashier chooses payment method(s)

Input amounts

Verify total matches cart total

Save sale + sale_items + sale_payments + inventory_movements + cash_movements in one transaction

Print / show receipt

Update drawer totals

Rules / safeguards:

Cannot process checkout if no open shift

Cannot process if cash drawer has discrepancy (optional alert)

Inventory ledger updated atomically with sale

5. Receipt / Sale Confirmation

Page / modal after checkout

Information:

Sale number

Items purchased

Quantity and line totals

Total paid

Payment method(s)

Cashier and shift info

Timestamp (completed_at)

Optional: QR code or copy for mobile payment confirmation

Flow:

Option to print receipt

Option to start new sale immediately

Return to POS Dashboard

6. Shift Close / Reconciliation

End of shift workflow

Page / modal:

List all sales for the shift

Calculate:

Opening cash + cash sales + top-ups − payouts

Compare to closing cash physically counted

Flag discrepancy

Optionally require manager approval for variance

Flow logic:

Cashier counts cash

Enter counted amount → shift.closing_cash

System calculates variance

Record all final cash_movements

Lock shift → no more sales can be assigned to it

Redirect to login page for next cashier

7. Optional Auxiliary Screens

Refund / Void Sale

Must be tied to open shift and require a reason

Adjust inventory_movements + cash_movements accordingly

Reports / Dashboard

Shift totals

Sales by cashier / product / category

Inventory shrink / restock alerts

8. Recommended Flow Diagram (linear)
Login
   │
   ▼
[Open Shift?] ──No──> Open Shift Form
   │Yes
   ▼
POS Dashboard / Add Products
   │
   ▼
Checkout / Payment
   │
   ▼
Receipt / New Sale
   │
   ▼
[End of Shift?] ──Yes──> Shift Close / Reconciliation
   │No
   ▼
POS Dashboard (continue selling)

9. Notes for high-volume / multi-terminal

Session management: Each cashier’s cart must be tied to user + shift

Atomic operations: Sales, inventory, payments → single DB transaction

Concurrency: Inventory ledger ensures no two cashiers oversell same product

Speed: UI must minimize clicks, keyboard-centric or barcode-focused

Audit trail: Everything linked to user_id and shift_id

# TODOS
- Fix auth flash messages.
- Add a button for super admins to verify users.
- Functionality for searching users.
- Functionality for filtering users.
- Functionality for sorting users.

~~- Live update users list and count.~~
~~- Stats for users on user.index page.~~
~~- CRUD users.~~



# FEATURES
- Authentication and Role-Based Authorization.
- User Management.
- Contact Form Messages.



# DB DESIGN
```
users {
    id();
    uuid('uuid')->unique();
    string('first_name');
    string('last_name');
    string('email')->unique();
    string('phone_number')->nullable();
    string('secondary_phone_number')->nullable();
    unsignedTinyInteger('role')->default(3);
    boolean('status')->default(1);
    string('image')->nullable();
    timestamp('email_verified_at')->nullable();
    string('password');
    rememberToken();
    timestamps();
}

contact_messages {
    id();
    string('name');
    string('email');
    string('phone_number');
    string('message', 2000);
    boolean('is_read')->default(0);
    boolean('is_important')->default(0);
    string('notes')->nullable();
    timestamps();
}
```



# ENUMS
```php
USER_ROLES: [
    SUPER_ADMIN = 0;
    ADMIN = 1;
    OWNER = 2;
    USER = 3;
]

USER_STATUSES: [
    INACTIVE = 0;
    ACTIVE = 1;
    BANNED = 2;
]

INVENTORY_MOVEMENT_TYPE: [
    SALE = 'sale';
    RESTOCK = 'restock';
    ADJUSTMENT = 'adjustment';
]

SALE_STATUS: [
    PENDING = 0;
    COMPLETED = 1;
]

protected $casts = [
    'role' => UserRole::class,
    'status' => UserStatus::class,
];
```

# Migrations
```php
// users
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->string('name');
    $table->string('email')->unique();
    $table->unsignedTinyInteger('role')->default(4)->index();
    $table->unsignedTinyInteger('status')->default(1)->index();
    $table->string('image')->nullable();
    $table->timestamp('email_verified_at')->nullable();
    $table->timestamp('last_login_at')->nullable();
    $table->string('password');
    $table->rememberToken();
    $table->timestamps();
});

Schema::create('branches', function (Blueprint $table) {
    $table->id();

    $table->uuid('uuid')->unique();
    $table->string('name');
    $table->string('code')->unique();
    $table->string('phone')->nullable();
    $table->string('email')->nullable();
    $table->string('address')->nullable();
    $table->string('city')->nullable();
    $table->boolean('is_active')->default(true);

    $table->timestamps();
});

Schema::create('staff_profiles', function (Blueprint $table) {
    $table->id();
    $table->string('staff_code')->unique();
    $table->string('position')->index(); // Cashier, Manager, Team Leader
    $table->timestamp('hired_at')->nullable();

    $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

    $table->timestamps();
});

Schema::create('customer_profiles', function (Blueprint $table) {
    $table->id();
    $table->string('customer_code')->nullable()->unique();
    $table->unsignedInteger('loyalty_points')->default(0);
    $table->decimal('credit_limit', 12, 2)->nullable();

    $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

    $table->timestamps();
});

Schema::create('supplier_profiles', function (Blueprint $table) {
    $table->id();
    $table->string('company_name');
    $table->string('payment_terms')->index(); // net_30, net_60, prepaid
    $table->string('tax_id')->nullable();
    $table->boolean('is_active')->default(true);

    $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

    $table->timestamps();
});

Schema::create('shifts', function (Blueprint $table) {
    $table->id();
    $table->timestamp("opened_at");
    $table->timestamp("closed_at")->nullable();
    $table->decimal("opening_cash", 12, 2)->nullable();
    $table->decimal("closing_cash", 12, 2)->nullable();

    $table->foreignId("user_id")->constrained()->cascadeOnDelete();

    $table->timestamps();

    $table->index(['user_id', 'opened_at']);
});

Schema::create('product_categories', function (Blueprint $table) {
    $table->id();

    $table->string('name');
    $table->string('slug')->unique();

    // For nested categories (e.g. Drinks → Soft Drinks)
    $table->foreignId('parent_id')->nullable()->constrained('product_categories')->nullOnDelete();

    $table->boolean('is_active')->default(true);
    $table->integer('sort_order')->default(0);

    $table->timestamps();
});

Schema::create('products', function (Blueprint $table) {
    $table->id();

    $table->string('name');
    $table->string('sku')->unique()->nullable();
    $table->decimal('buying_price', 12, 2)->nullable(); // For profit calculation
    $table->decimal('selling_price', 12, 2);
    $table->string('barcode')->unique()->nullable();
    $table->boolean('is_active')->default(true);
    $table->integer('current_stock')->default(0);
    $table->string('unit_of_measurement')->nullable(); // For receipt display

    $table->timestamps();
});

Schema::create('category_product', function (Blueprint $table) {
    $table->id();

    $table->foreignId('category_id')->constrained()->cascadeOnDelete();

    $table->foreignId('product_id')->constrained()->cascadeOnDelete();

    $table->unique(['category_id', 'product_id']);
});

Schema::create('product_images', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->boolean('is_primary')->default(false);
    $table->integer('sort_order')->default(0);

    $table->foreignId('product_id')->constrained()->cascadeOnDelete();

    $table->timestamps();
});

Schema::create('inventory_movements', function (Blueprint $table) {
    $table->id();

    $table->unsignedTinyInteger('type'); // sale, restock, adjustment, return, waste
    $table->integer('quantity_change'); // Positive for in, negative for out
    $table->text('reason')->nullable(); // "stock take", "damaged"
    $table->string('reference_type')->nullable(); // "App\Models\|Sale"

    $table->foreignId('reference_id')->nullable(); // Links to purchese_order_id, sale_id
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();

    $table->timestamp('created_at');

    $table->index(['product_id', 'reference_type', 'reference_id', 'created_at']);
});

Schema::create('sales', function (Blueprint $table) {
    $table->id();

    $table->string('sale_number')->unique();
    $table->unsignedTinyInteger('status')->default(1);
    $table->decimal('total_amount', 12, 2);

    $table->foreignId('shift_id')->constrained()->restrictOnDelete();
    $table->foreignId('user_id')->constrained()->restrictOnDelete();
    
    $table->timestamp('completed_at');
    $table->timestamps();

    $table->index(['shift_id', 'completed_at']);
});

Schema::create('sale_items', function (Blueprint $table) {
    $table->id();

    $table->foreignId('sale_id')->constrained()->cascadeOnDelete();

    $table->foreignId('product_id')->constrained()->restrictOnDelete();

    $table->string('product_name');
    $table->string('sku');

    $table->decimal('unit_price', 12, 2);
    $table->integer('quantity');
    $table->decimal('line_total', 12, 2);

    $table->timestamps();
});

Schema::create('sale_payments', function (Blueprint $table) {
    $table->id();

    $table->string('method'); // cash, card, mpesa
    $table->decimal('amount', 12, 2);
    
    $table->string('reference')->nullable();

    $table->foreignId('sale_id')->constrained()->cascadeOnDelete();

    $table->timestamps();
});

Schema::create('cash_movements', function (Blueprint $table) {
    $table->id();
    
    $table->string('type'); // opening, sale, payout, topup, closing
    $table->decimal('amount', 12, 2);
    
    $table->string('note')->nullable();
    
    $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->restrictOnDelete();

    $table->timestamps();
});
```


# MODELS
```php
class User extends Authenticatable
{
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function staffProfile()
    {
        return $this->hasOne(StaffProfile::class);
    }

    public function customerProfile()
    {
        return $this->hasOne(CustomerProfile::class);
    }

    public function supplierProfile()
    {
        return $this->hasOne(SupplierProfile::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }
}

```