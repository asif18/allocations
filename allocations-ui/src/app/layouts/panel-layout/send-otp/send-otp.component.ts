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
import { Component, OnInit, OnDestroy } from '@angular/core';
import { FormBuilder, FormGroup, Validators, FormControl } from '@angular/forms';
import { noop, Subscription, forkJoin } from 'rxjs';
import { finalize } from 'rxjs/operators';
import { ActivatedRoute } from '@angular/router';
import { Title } from '@angular/platform-browser';
import {
  SnackbarService,
  RoomService,
  ProfileService,
  DataUsageLimitsService,
  YardsService,
  SendOtpService
  } from '../../../_services';
import { DefaultApiResponse, DefaultListApiParams } from '../../../_models';
import * as _ from 'lodash';

declare interface FormDependencyData {
  rooms: object[];
  profiles: object[];
  dataUsageLimits: object[];
  usageTimingLimits: object[];
}

@Component({
  selector: 'app-send-otp',
  templateUrl: './send-otp.component.html',
  styleUrls: ['./send-otp.component.scss']
})
export class SendOtpComponent implements OnInit, OnDestroy {

  public isFormLoading: boolean = false;
  public form: FormGroup;
  private submitted = false;
  private subscriptions: Subscription[] = [];
  public hasRoomIdInUrl: boolean = false;
  public formDependencyData: FormDependencyData = {
    rooms: [],
    profiles: [],
    dataUsageLimits: [],
    usageTimingLimits: []
  };
  private roomId: string = null;
  public cardTitle: string = 'Send OTP';

  constructor(
    private formBuilder: FormBuilder,
    private sendOtpService: SendOtpService,
    private roomService: RoomService,
    private profileService: ProfileService,
    private dataUsageLimitsService: DataUsageLimitsService,
    private usageTimingLimitsService: YardsService,
    private snackBar: SnackbarService,
    private route: ActivatedRoute,
    private titleService: Title
  ) { }

  ngOnInit() {
    this.titleService.setTitle('Send OTP');
    this.subscriptions.push(this.route.params.subscribe(params => {
      this.hasRoomIdInUrl = !!params.id;

      if (this.hasRoomIdInUrl) {
        try {
          this.roomId = atob(params.id);
        } catch (error) { this.hasRoomIdInUrl = false; noop(); }
      }
    }));
    this.initForm();
    this.getFormInitialData();
  }

  private getFormInitialData() {
    this.isFormLoading = true;
    const params: DefaultListApiParams = {
      searchBy: null,
      startFrom: null,
      endTo: null,
      sortBy: 'size',
      sortDirection: 'desc',
    };
    const requests = forkJoin(
      this.roomService.getRooms(),
      this.profileService.getProfiles(),
      this.dataUsageLimitsService.getDataUsageLimits(params),
      this.usageTimingLimitsService.getUsageTimingLimits(_.merge(_.clone(params), {sortBy: 'time'}))
    );

    this.subscriptions.push(requests
      .pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: DefaultApiResponse[]) => {
        this.formDependencyData = {
          rooms: _.orderBy(response[0].data.items, ['name'], ['asc']),
          profiles: response[1].data.items,
          dataUsageLimits: response[2].data.items,
          usageTimingLimits: response[3].data.items
        };
        if (this.hasRoomIdInUrl) {
          const room = _.find(this.formDependencyData.rooms, {'.id': this.roomId});
          const cardTitle = `Send OTP to ${room.name}`;
          this.titleService.setTitle(cardTitle);
          this.cardTitle = cardTitle;
          this.form.patchValue({room});
        }
      }));
  }

  private initForm() {
    this.form = this.formBuilder.group({
      room: new FormControl(null, {
        validators: [
          Validators.required
        ]
      }),
      name: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.maxLength(35),
          Validators.minLength(3),
          Validators.pattern(/^[ a-zA-Z0-9]+$/)
        ]
      }),
      phone: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.maxLength(10),
          Validators.minLength(10),
          Validators.pattern(/^[0-9]+$/)
        ]
      }),
      autoPassword: new FormControl(false),
      password: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.maxLength(15),
          Validators.minLength(5),
          Validators.pattern(/^\S*$/)
        ]
      }),
      profile: new FormControl(null, {
        validators: [
          Validators.required
        ]
      }),
      dataUsageLimit: new FormControl(null, {
        validators: [
          Validators.required
        ]
      }),
      checkOutTime: new FormControl(null, {
        validators: [
          Validators.required
        ]
      })
    });
  }

  get f() {
    return this.form.controls;
  }

  onSubmit() {
    this.submitted = true;

    if (this.form.invalid) {
      return;
    }

    this.isFormLoading = true;

    this.form.value.password = btoa(this.form.value.password);

    this.subscriptions.push(this.sendOtpService.sendOtp(this.form.value)
      .pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: DefaultApiResponse) => {
        if (response) {
          this.snackBar.show(response.message, response.status ? 'success' : 'danger');
        }
      }, () => noop()));
  }

  ngOnDestroy() {
    this.subscriptions.forEach(subscription => subscription.unsubscribe());
  }
}
