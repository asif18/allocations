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
import { Component, OnInit, ViewChild, ElementRef, OnDestroy } from '@angular/core';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { Subscription } from 'rxjs';
import { finalize } from 'rxjs/operators';
import { Title } from '@angular/platform-browser';
import * as _ from 'lodash';
import { SnackbarService, ProfileService, UtilityService } from '../../../_services';
import { DefaultApiResponse } from '../../../_models';

@Component({
  selector: 'app-profile-list',
  templateUrl: './profile-list.component.html',
  styleUrls: ['./profile-list.component.scss']
})
export class ProfileListComponent implements OnInit, OnDestroy {

  public displayedColumns: string[] = ['.id', 'name', 'shared-users', 'add-mac-cookie', 'mac-cookie-timeout', 'session-timeout',
    'rate-limit', 'action'];
  public data: MatTableDataSource<any> = null;
  public isFormLoading: boolean = true;
  public resultsLength = 0;
  private subscriptions: Subscription[] = [];

  @ViewChild(MatPaginator, {static: true}) paginator: MatPaginator;
  @ViewChild(MatSort, {static: true}) sort: MatSort;
  @ViewChild('searchInput', {static: true}) searchInput: ElementRef;

  constructor(
    private titleService: Title,
    private snackBar: SnackbarService,
    private utilityService: UtilityService,
    private profileService: ProfileService) {}

  ngOnInit(): void {
    this.titleService.setTitle('Profile List');
    this.getProfiles();
  }

  private getProfiles() {
    this.subscriptions.push(this.profileService.getProfiles()
      .pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: DefaultApiResponse) => {
        if (response.status) {
          this.resultsLength = _.size(response.data.items);
          this.data = new MatTableDataSource(response.data.items);
          this.data.paginator = this.paginator;
          this.data.sort = this.sort;
        } else {
          this.snackBar.show(response.message, response.status ? 'success' : 'danger');
        }
      }));
  }

  applyFilter(filterValue: string) {
    this.data.filter = filterValue.trim().toLowerCase();

    if (this.data.paginator) {
      this.data.paginator.firstPage();
    }
  }

  resetSearchValue(): void {
    this.searchInput.nativeElement.value = '';
    this.applyFilter('');
  }

  removeProfile(id: string): void {
    this.isFormLoading = true;
    this.subscriptions.push(this.profileService.removeProfile(btoa(id))
    .pipe(finalize(() => this.isFormLoading = false))
    .subscribe((response: DefaultApiResponse) => {
      this.getProfiles();
      this.snackBar.show(response.message, response.status ? 'success' : 'danger');
    }));
  }

  ngOnDestroy() {
    this.subscriptions.forEach(subscription => subscription.unsubscribe());
  }
}