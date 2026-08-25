<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waitlist_entries', function (Blueprint $table) {
            $table->uuid('benefit_id')->nullable()->unique()->after('id');
            $table->unsignedBigInteger('waitlist_position')->nullable()->unique()->after('email');
            $table->timestamp('email_verified_at')->nullable()->after('waitlist_position');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignUuid('waitlist_entry_id')
                ->nullable()
                ->after('id')
                ->constrained('waitlist_entries')
                ->nullOnDelete();
        });

        $position = 0;
        DB::table('waitlist_entries')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->each(function (object $entry) use (&$position): void {
                $position++;
                DB::table('waitlist_entries')->where('id', $entry->id)->update([
                    'benefit_id' => (string) Str::uuid(),
                    'waitlist_position' => $position,
                    'email_verified_at' => $entry->phone_verified_at ?? $entry->created_at ?? now(),
                ]);
            });

        DB::table('purchases')
            ->orderBy('created_at')
            ->get()
            ->each(function (object $purchase): void {
                $waitlistEntryId = DB::table('waitlist_entries')
                    ->whereRaw('LOWER(email) = ?', [Str::lower((string) $purchase->email)])
                    ->value('id');

                if ($waitlistEntryId) {
                    DB::table('purchases')->where('id', $purchase->id)->update([
                        'waitlist_entry_id' => $waitlistEntryId,
                    ]);
                }
            });

        Schema::create('waitlist_email_verifications', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(gen_random_uuid())'));
            $table->foreignUuid('waitlist_entry_id')->constrained('waitlist_entries')->cascadeOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at')->index();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['waitlist_entry_id', 'created_at']);
        });

        Schema::create('benefit_email_challenges', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(gen_random_uuid())'));
            $table->foreignUuid('waitlist_entry_id')->nullable()->constrained('waitlist_entries')->cascadeOnDelete();
            $table->string('email_hash', 64)->index();
            $table->string('code_hash', 64);
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at')->index();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('benefit_claims', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(gen_random_uuid())'));
            $table->foreignUuid('waitlist_entry_id')->unique()->constrained('waitlist_entries')->cascadeOnDelete();
            $table->string('account_reference', 255);
            $table->string('idempotency_key', 255)->unique();
            $table->string('benefit_type', 32);
            $table->timestamp('claimed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('benefit_claims');
        Schema::dropIfExists('benefit_email_challenges');
        Schema::dropIfExists('waitlist_email_verifications');

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('waitlist_entry_id');
        });

        Schema::table('waitlist_entries', function (Blueprint $table) {
            $table->dropUnique(['benefit_id']);
            $table->dropUnique(['waitlist_position']);
            $table->dropColumn(['benefit_id', 'waitlist_position', 'email_verified_at']);
        });
    }
};
