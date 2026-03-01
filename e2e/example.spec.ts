import { test, expect } from '@playwright/test';

test('homepage loads and has correct title', async ({ page }) => {
  await page.goto('./');

  // Expect the title to contain the app name
  await expect(page).toHaveTitle(/Student Satisfaction Survey/);
});

test('about page is accessible', async ({ page }) => {
  await page.goto('./about.php');

  // Expect the page to load with About Us title
  await expect(page).toHaveTitle(/About/);
});

test('contact page is accessible', async ({ page }) => {
  await page.goto('./contact.php');

  // Expect the contact page to load
  await expect(page).toHaveTitle(/Contact/);
});
