# DB DESIGN
```
users {
	$table->id();
	$table->string('name');
	$table->string('email')->unique();
	$table->timestamp('email_verified_at')->nullable();
	$table->string('password');
	$table->rememberToken();
	$table->timestamps();
}

tasks {
	id();
	string('name');
	text('description')->nullable();
	unsignedTinyInteger('priority')->default(0);
	unsignedTinyInteger('status')->default(0);
	date('started')->nullable();
	date('finished')->nullable();
	date('deadline')->nullable();

	foreignId('user_id')->constrained('users')->cascadeOnDelete();
	timestamps();
}

sub_tasks {
	id();
	string('name');
	unsignedTinyInteger('priority')->default(0);
	unsignedTinyInteger('status')->default(0);
	unsignedInteger('sort_order')->nullable();

	foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
	timestamps();
}

monthly_expenses_categories {
	id();
	string('name')->unique();
}

monthly_expenses {
	id();
	decimal('amount', 10, 2);
	string('note')->nullable();
	date('expense_date');

	foreignId('category_id')->constrained('monthly_expenses_categories')->cascadeOnDelete();
	foreignId('user_id')->constrained('users')->cascadeOnDelete();
	timestamps();
}

account_balances {
	id();
	string('name');
	decimal('balance', 14, 2)->default(0.00);
	date('date');

	foreignId('user_id')->constrained('users')->cascadeOnDelete();
	timestamps();
}

vault_labels {
	id();
	string('name');
}

vaults {
	id();
	text('value');
	string('type')->default('text');

	foreignId('label_id')->constrained('vault_labels')->cascadeOnDelete();
	foreignId('user_id')->constrained('users')->cascadeOnDelete();
	timestamps();
}
```

# ENUMS
```
enum TaksPriority: int
{
	case LOW = 0;
	case MEDIUM = 1;
	case HIGH = 2;
}

enum TaskStatus: int
{
	case NOT_STARTED = 0;
    case IN_PROGRESS = 1;
    case COMPLETE = 2;
}
```
