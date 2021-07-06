/**
 * Copyrights Allocations 2021. All rights reserved
 *
 * The code, text and other elements of this application/file is copyrighted
 * You may not remove any copyright or other proprietary notices contained in this file
 * The rights granted to you use this application in your organization for your
 * business/personal purpose and not to sell or modify
 *
 * Developed by: Mohamed Asif
 * Email: mohamedasif18@gmail.com
 */
import { Routes } from '@angular/router';
import { LoginComponent } from './login';
import { PageNotFoundComponent } from './page-not-found';

export const APP_ROUTES: Routes = [
  { path: '', redirectTo: 'login', pathMatch: 'full' },
  { path: 'login', component: LoginComponent },
  { path: 'panel', loadChildren: () => import('./layouts/panel-layout/panel-layout.module').then(m => m.PanelLayoutModule)},
  { path: '**', component: PageNotFoundComponent }
];
