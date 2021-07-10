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
import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { ComponentsModule } from '../../_components';
import { ChartModule } from 'angular-highcharts';
import { PanelLayoutComponent } from './panel-layout.component';
import { DashboardComponent } from './dashboard';
import { PanelLayoutRoutes } from './panel-layout.routes';
import { AuthGuard } from '../../_guards';
import { DashboardResolver } from '../../_classes';
import { MaterialModule } from '../../material.module';

import { AllocationsComponent } from './allocations';
import { AllocationsListComponent } from './allocations-list';
import { AllocatedListComponent } from './allocated-list';
import { DeliveredListComponent } from './delivered-list';
import { YardsComponent } from './yards';
import { DestinationsComponent } from './destinations';
import { StaffsListComponent } from './staffs-list';
import { SettingsComponent } from './settings';
import { DisableControlDirective, UppercaseControlDirective } from '../../_directives';

@NgModule({
  imports: [
    BrowserModule,
    CommonModule,
    ComponentsModule,
    MaterialModule,
    ChartModule,
    FormsModule,
    FontAwesomeModule,
    ReactiveFormsModule,
    RouterModule.forChild(PanelLayoutRoutes)
  ],
  declarations: [
    PanelLayoutComponent,
    DashboardComponent,
    SettingsComponent,
    AllocationsComponent,
    AllocationsListComponent,
    AllocatedListComponent,
    DeliveredListComponent,
    YardsComponent,
    DestinationsComponent,
    StaffsListComponent,
    DisableControlDirective,
    UppercaseControlDirective
  ],
  providers: [
    AuthGuard,
    DashboardResolver
  ]
})

export class PanelLayoutModule {}
