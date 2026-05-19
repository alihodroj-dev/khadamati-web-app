<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            MunicipalitySeeder::class,
            OfficeSeeder::class,
            UserSeeder::class,
            ServiceCategorySeeder::class,
            ServiceSeeder::class,
            OfficeTimeSlotSeeder::class,
            OfficeTimeSlotBlockSeeder::class,
            ServiceRequestSeeder::class,
            RequestDocumentSeeder::class,
            AppointmentSeeder::class,
            PaymentSeeder::class,
            FeedbackSeeder::class,
            FeedbackResponseSeeder::class,
            ConversationSeeder::class,
            MessageSeeder::class,
            DeviceTokenSeeder::class,
            SocialAccountSeeder::class,
            OtpChallengeSeeder::class,
            IdentityVerificationSessionSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
