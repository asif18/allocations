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
import { Component, ViewChild, OnInit, AfterViewInit, ElementRef, OnDestroy } from '@angular/core';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatBottomSheet } from '@angular/material/bottom-sheet';
import { merge, Subscription, of as observableOf, fromEvent } from 'rxjs';
import {
  catchError,
  map,
  startWith,
  switchMap,
  debounceTime,
  distinctUntilChanged,
  tap,
  finalize } from 'rxjs/operators';
import { Title } from '@angular/platform-browser';
import { Router } from '@angular/router';
import { faFileExcel } from '@fortawesome/free-solid-svg-icons';
import { UserService, DataShareService, UtilityService } from '../../../_services';
import { DefaultListApiParams } from '../../../_models';
import { BottomSheetFileDownloadPromptComponent } from '../../../_components/bottom-sheet-file-download-prompt';
import * as _ from 'lodash';

declare interface Users {
  id: number;
  name: string;
  mikIp: string;
  mikPort: string;
  mikUsername: string;
  mikPassword: string;
  mikLanIp: string;
  destination: string;
  datetime: string;
  businessName: string;
}

@Component({
  selector: 'app-users-list',
  templateUrl: './users-list.component.html',
  styleUrls: ['./users-list.component.scss']
})
export class UsersListComponent implements OnInit, AfterViewInit, OnDestroy  {

  public displayedColumns: string[] = ['name', 'email', 'phone', 'datetime'];
  public data: Users[] = [];
  public isFormLoading: boolean = true;
  public resultsLength = 0;
  public pageSize: number = 10;
  private subscriptions: Subscription[] = [];
  public faIcons = {faFileExcel};

  @ViewChild(MatPaginator, {static: true}) paginator: MatPaginator;
  @ViewChild(MatSort, {static: true}) sort: MatSort;
  @ViewChild('searchInput', {static: true}) searchInput: ElementRef;

  constructor(
    private userService: UserService,
    private titleService: Title,
    private dataShareService: DataShareService,
    private bottomSheet: MatBottomSheet,
    private utilityService: UtilityService,
    private router: Router) {}

  ngOnInit(): void {
    this.titleService.setTitle('Users List');
    this.subscriptions.push(this.dataShareService.receivedMessage.subscribe(data => {
      if (_.isNull(data)) {
        return;
      }

      const params: DefaultListApiParams =  {
        searchBy: this.searchInput.nativeElement.value,
        startFrom: this.paginator.pageIndex,
        endTo: this.paginator.pageIndex + this.paginator.pageSize,
        sortBy: this.sort.active,
        sortDirection: this.sort.direction,
      };

      switch(data) {
        case 'downloadVisibled':
          break;

        case 'downloadFiltered':
          params.startFrom = null;
          params.endTo = null;
          break;

        case 'downloadAll':
          params.searchBy = null;
          params.startFrom = null;
          params.endTo = null;
          break;
      }

      this.isFormLoading = true;
      this.userService.exportUsers(params)
        .pipe(finalize(() => this.isFormLoading = false))
        .subscribe((response) => {
          const fileName = this.utilityService.getFileNameFromHttpResponse(response);
          const options = { type: response.body.type };
          this.utilityService.downloadBlobFile(response.body, options, fileName);
        });
    }));
  }

  ngAfterViewInit() {
    // If the user changes the sort order, reset back to the first page.
    this.sort.sortChange.subscribe(() => this.paginator.pageIndex = 0);

    fromEvent(this.searchInput.nativeElement, 'keyup')
    .pipe(
      debounceTime(150),
      distinctUntilChanged(),
      switchMap(() => {
        this.isFormLoading = true;
        this.paginator.pageIndex = 0;
        return this.getUsers();
      }),
      map(response => {
        this.isFormLoading = false;
        this.resultsLength = _.size(response.data.items);
        return response.data.items;
      }),
      catchError(() => {
        return observableOf([]);
      })
    ).subscribe(data => this.data = data);

    merge(this.sort.sortChange, this.paginator.page)
      .pipe(
        startWith({}),
        switchMap(() => {
          this.isFormLoading = true;
          return this.getUsers();
        }),
        map(response => {
          this.isFormLoading = false;
          this.resultsLength = _.size(response.data.items);
          return response.data.items;
        }),
        catchError(() => {
          return observableOf([]);
        })
      ).subscribe(data => this.data = data);
  }

  private getUsers() {
    const params: DefaultListApiParams = {
      searchBy: this.searchInput.nativeElement.value,
      startFrom: this.paginator.pageIndex,
      endTo: this.paginator.pageIndex + this.paginator.pageSize,
      sortBy: this.sort.active,
      sortDirection: this.sort.direction,
    };
    return this.userService.getUsers(params);
  }

  resetSearchValue(): void {
    this.searchInput.nativeElement.value = '';
    const event = new KeyboardEvent('keyup', {bubbles: true});
    this.searchInput.nativeElement.dispatchEvent(event);
  }

  openBottomSheet(): void {
    const items: object[] = [
      {
        action: 'downloadVisibled',
        title: 'Download What You See',
        description: 'downloads excel sheet with data that is visible in this page'
      },
      {
        action: 'downloadFiltered',
        title: 'Download Filtered',
        description: 'downloads excel sheet with data that is filtered if any'
      },
      {
        action: 'downloadAll',
        title: 'Download All',
        description: 'downloads excel sheet with entire data'
      }
    ];
    this.bottomSheet.open(BottomSheetFileDownloadPromptComponent, {
      data: {component: 'UsersListComponent', items}
    });
  }

  navigate(id: string) {
    this.router.navigate([`panel/instance/${btoa(id)}`]);
  }

  decryptPassword(data: string): string {
    return atob(data);
  }
  ngOnDestroy() {
    this.subscriptions.forEach(subscription => subscription.unsubscribe());
  }
}
