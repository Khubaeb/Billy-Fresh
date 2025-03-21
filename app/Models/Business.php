<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'business_id',
        'logo',
        'address',
        'phone',
        'email',
        'tax_number',
        'registration_number',
        'website',
        'settings',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'currency',
        'logo_path',
        'notes',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'json',
        'is_active' => 'boolean'
    ];

    /**
     * Get the users that belong to the business.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withPivot('role_id')
            ->using(UserRole::class);
    }

    /**
     * Get the roles that belong to the business.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot('user_id')
            ->using(UserRole::class);
    }

    /**
     * Get the customers for the business.
     */
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Get the services for the business.
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get the invoices for the business.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the expenses for the business.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get the recurring billings for the business.
     */
    public function recurringBillings()
    {
        return $this->hasMany(RecurringBilling::class);
    }

    /**
     * Get the payment methods for the business.
     */
    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Get the expense categories for the business.
     */
    public function expenseCategories()
    {
        return $this->hasMany(ExpenseCategory::class);
    }

    /**
     * Get the tax rates for the business.
     */
    public function taxRates()
    {
        return $this->hasMany(TaxRate::class);
    }

    /**
     * Get the document templates for the business.
     */
    public function documentTemplates()
    {
        return $this->hasMany(DocumentTemplate::class);
    }

    /**
     * Get the activity logs for the business.
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Get the notifications for the business.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the business settings.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getSetting($key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Set a business setting.
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function setSetting($key, $value)
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;

        return $this;
    }
}
