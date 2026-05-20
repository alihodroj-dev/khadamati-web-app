<?php

namespace Database\Seeders\Concerns;

trait SeedImageUrls
{
    protected function unsplash(string $photoId): string
    {
        return "https://images.unsplash.com/{$photoId}?w=640&h=400&fit=crop";
    }

    protected function officeImageUrl(string $officeName): string
    {
        return match ($officeName) {
            'Beirut Central Services Office' => $this->unsplash('photo-1486406146928-c627a92fd1f2'),
            'Hamra Citizen Service Center' => $this->unsplash('photo-1449823893825-98062c0847b1'),
            'Tripoli Main Office' => $this->unsplash('photo-1565008576549-b5b1c4c86eb8'),
            'Sidon Services Office' => $this->unsplash('photo-1590736969955-71cc94901144'),
            default => $this->unsplash('photo-1480714378408-c6627a496933'),
        };
    }

    protected function categoryImageUrl(string $categoryName): string
    {
        return match ($categoryName) {
            'Civil Records' => $this->unsplash('photo-1450101499168-c8c11fb94710'),
            'Municipality Services' => $this->unsplash('photo-1480714378408-c6627a496933'),
            'Tax Services' => $this->unsplash('photo-1554224154-26032ff0bdd0'),
            'Health Services' => $this->unsplash('photo-1576091160399-2df915bbef09'),
            'Education Services' => $this->unsplash('photo-1523050853118-f07a6adfcef0'),
            default => $this->unsplash('photo-1454165804606-c3d57bc86b40'),
        };
    }

    protected function serviceImageUrl(string $serviceName): string
    {
        return match ($serviceName) {
            'Birth Certificate Request' => $this->unsplash('photo-1511895426328-dc8714191300'),
            'Marriage Certificate Request' => $this->unsplash('photo-1519742849674-bd93f090a656'),
            'Family Record Statement' => $this->unsplash('photo-1586281380349-632531db7bb4'),
            'Building Permit Request' => $this->unsplash('photo-1503387762-592deb58ef03'),
            'Municipal Complaint' => $this->unsplash('photo-1558618666-fcd25c85cd64'),
            'Occupancy Certificate Request' => $this->unsplash('photo-1560518883-ce09059eeffa'),
            'Tax Clearance Request' => $this->unsplash('photo-1554224154-26032ff0bdd0'),
            'Property Tax Statement' => $this->unsplash('photo-1560520032-3a64f8b64c5e'),
            'Health Coverage Certificate' => $this->unsplash('photo-1576091160399-2df915bbef09'),
            'Medical Assistance Request' => $this->unsplash('photo-1631217861164-6a440d223a1e'),
            'Student Enrollment Certificate' => $this->unsplash('photo-1523050853118-f07a6adfcef0'),
            'Scholarship Assistance Request' => $this->unsplash('photo-1523240795612-9a054b0db644'),
            default => $this->unsplash('photo-1454165804606-c3d57bc86b40'),
        };
    }
}
