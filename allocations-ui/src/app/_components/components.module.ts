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
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { MaterialModule } from '../material.module';
import { FooterComponent } from './footer';
import { NavbarComponent } from './navbar';
import { SidebarComponent } from './sidebar';
import { PreloaderComponent } from '../preloader/preloader.component';
import { BottomSheetFileDownloadPromptComponent } from './bottom-sheet-file-download-prompt';
import { ConfirmationDialogComponent } from './confirmation-dialog';

@NgModule({
  entryComponents: [
    BottomSheetFileDownloadPromptComponent,
    ConfirmationDialogComponent
  ],
  imports: [
    CommonModule,
    RouterModule,
    MaterialModule
  ],
  declarations: [
    FooterComponent,
    NavbarComponent,
    SidebarComponent,
    PreloaderComponent,
    BottomSheetFileDownloadPromptComponent,
    ConfirmationDialogComponent
  ],
  exports: [
    FooterComponent,
    NavbarComponent,
    SidebarComponent,
    PreloaderComponent
  ]
})
export class ComponentsModule { }
